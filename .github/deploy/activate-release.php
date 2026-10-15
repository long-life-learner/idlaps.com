<?php

/** Invoked only by the cPanel CLI cron, outside the web document root. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

umask(0022);
$base = __DIR__;
$lock = fopen($base.'/deploy.lock', 'c');
if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
    exit;
}
$pending = $base.'/incoming/pending.json';
if (! is_file($pending)) {
    exit;
}
$release = null;
try {
    if (PHP_VERSION_ID < 80300 || ! class_exists(ZipArchive::class)) {
        throw new RuntimeException('PHP 8.3+ and the zip extension are required.');
    }
    $job = json_decode(file_get_contents($pending), true, flags: JSON_THROW_ON_ERROR);
    $release = $job['release'] ?? '';
    if (! is_string($release) || ! preg_match('/\A[0-9a-f]{40}-[0-9]+-[0-9]+\z/', $release)) {
        throw new RuntimeException('Invalid release identifier.');
    }
    $archive = $base.'/incoming/idlaps-'.$release.'.zip';
    if (! is_string($job['sha256'] ?? null) || ! hash_equals($job['sha256'], hash_file('sha256', $archive))) {
        throw new RuntimeException('Release checksum mismatch.');
    }
    $isProduction = ($job['target'] ?? 'staging') === 'production';
    $productionRoot = dirname($base, 2).'/public_html';
    if ($isProduction && (basename(dirname($base)) !== 'idlaps.com' || ! is_dir($productionRoot) || ! is_writable($productionRoot))) {
        throw new RuntimeException('Production document root is unavailable.');
    }
    $target = $base.'/releases/'.$release;
    if (file_exists($target)) {
        throw new RuntimeException('Release already exists. Dispatch a fresh workflow attempt.');
    }
    if (! is_file($base.'/.env')) {
        throw new RuntimeException('Private environment configuration is missing.');
    }
    $zip = new ZipArchive;
    if ($zip->open($archive) !== true) {
        throw new RuntimeException('Cannot open release archive.');
    }
    $total = 0;
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $entry = $zip->statIndex($index);
        $name = $entry['name'];
        $zip->getExternalAttributesIndex($index, $system, $attributes);
        $total += $entry['size'];
        if (str_starts_with($name, '/') || str_contains($name, '\\') || preg_match('~(^|/)\.\.(/|$)~', $name) || (($attributes >> 16) & 0170000) === 0120000 || $total > 600_000_000) {
            throw new RuntimeException('Unsafe archive entry or oversized release.');
        }
    }
    mkdir($target, 0755, true);
    if (! $zip->extractTo($target)) {
        throw new RuntimeException('Cannot extract release.');
    }
    $zip->close();
    if (trim(file_get_contents($target.'/REVISION')) !== substr($release, 0, 40)) {
        throw new RuntimeException('Release revision mismatch.');
    }
    foreach (['app/private', 'app/public', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $directory) {
        if (! is_dir($base.'/storage/'.$directory)) {
            mkdir($base.'/storage/'.$directory, 0755, true);
        }
    }
    rename($target.'/storage', $target.'/storage-package');
    if (! symlink($base.'/storage', $target.'/storage') || ! symlink($base.'/.env', $target.'/.env')) {
        throw new RuntimeException('Cannot link shared storage or environment.');
    }
    if ($isProduction) {
        $environment = file_get_contents($base.'/.env');
        $environment = preg_replace('/^APP_URL=.*$/m', 'APP_URL=https://idlaps.com', $environment);
        $environment = preg_replace('/^SITE_INDEXABLE=.*$/m', 'SITE_INDEXABLE=true', $environment);
        file_put_contents($base.'/.env.next', $environment);
        chmod($base.'/.env.next', 0600);
        rename($base.'/.env.next', $base.'/.env');
    }
    chdir($target);
    require $target.'/vendor/autoload.php';
    $app = require $target.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    foreach (['migrate' => ['--force' => true], 'db:seed' => ['--force' => true], 'config:cache' => [], 'route:cache' => [], 'view:cache' => []] as $command => $options) {
        if ($kernel->call($command, $options + ['--no-interaction' => true]) !== 0) {
            throw new RuntimeException('Artisan command failed: '.$command);
        }
    }
    if (! App\Models\User::where('is_admin', true)->exists()) {
        $password = bin2hex(random_bytes(20));
        $credentials = $base.'/storage/app/private/cms-login.txt';
        file_put_contents($credentials, "CMS: https://staging.idlaps.com/admin\nEmail: admin@idlaps.com\nPassword: {$password}\n");
        chmod($credentials, 0600);
        $admin = new App\Models\User(['name' => 'Administrator IDLAPS', 'email' => 'admin@idlaps.com', 'password' => $password]);
        $admin->is_admin = true;
        $admin->save();
    }
    $public = $base.'/public';
    if (is_dir($public) && ! is_link($public)) {
        $files = array_diff(scandir($public), ['.', '..', '.well-known']);
        if ($files !== []) {
            throw new RuntimeException('Existing public directory is not empty; activation stopped.');
        }
        if (is_dir($public.'/.well-known') && ! file_exists($base.'/.well-known')) {
            rename($public.'/.well-known', $base.'/.well-known');
        }
        rename($public, $base.'/public-before-'.$release);
    }
    if (is_dir($base.'/.well-known')) {
        symlink($base.'/.well-known', $target.'/public/.well-known');
    }
    if (! symlink($target.'/public', $base.'/public-next-'.$release) || ! rename($base.'/public-next-'.$release, $public)) {
        throw new RuntimeException('Cannot activate release.');
    }
    if ($isProduction) {
        $backup = $base.'/production-backups/'.$release;
        mkdir($backup, 0700, true);
        foreach (['build', 'media', 'favicon.ico', 'favicon.svg', 'robots.txt'] as $asset) {
            $destination = $productionRoot.'/'.$asset;
            $source = $public.'/'.$asset;
            if (is_link($destination) && readlink($destination) === $source) {
                continue;
            }
            if (file_exists($destination) || is_link($destination)) {
                if (! rename($destination, $backup.'/'.$asset)) {
                    throw new RuntimeException('Cannot back up public asset: '.$asset);
                }
            }
            if (! symlink($source, $destination)) {
                throw new RuntimeException('Cannot link public asset: '.$asset);
            }
        }
        foreach (['index.php' => 'production-index.php', '.htaccess' => 'production.htaccess'] as $filename => $template) {
            $destination = $productionRoot.'/'.$filename;
            if (is_file($destination)) {
                copy($destination, $backup.'/'.$filename);
            }
            if (! copy($base.'/'.$template, $destination.'.next') || ! rename($destination.'.next', $destination)) {
                throw new RuntimeException('Cannot update production entry point: '.$filename);
            }
        }
    }
    $result = ['release' => $release, 'status' => 'success', 'php' => PHP_VERSION];
    file_put_contents($base.'/incoming/result.json', json_encode($result, JSON_THROW_ON_ERROR));
    rename($pending, $base.'/incoming/completed-'.$release.'.json');
    echo "Release activated: {$release}\n";
} catch (Throwable $exception) {
    $result = ['release' => $release, 'status' => 'failed', 'error' => get_class($exception)];
    file_put_contents($base.'/incoming/result.json', json_encode($result));
    file_put_contents($base.'/deploy-error.log', date(DATE_ATOM).' '.$exception->getMessage()."\n", FILE_APPEND);
    chmod($base.'/deploy-error.log', 0600);
    if (is_file($pending)) {
        rename($pending, $base.'/incoming/failed-'.date('YmdHis').'.json');
    }
    fwrite(STDERR, "Deployment failed. Check the private deploy-error.log in cPanel.\n");
    exit(1);
}
