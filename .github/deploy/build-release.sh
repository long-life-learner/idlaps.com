#!/usr/bin/env bash
set -euo pipefail

# Only a fresh directory is accepted so an older package cannot leak into a release.
output="${1:?Provide an absolute output directory outside the checkout}"
[[ "$output" = /* && ! -e "$output" ]] || { echo 'Output must be a new absolute directory.' >&2; exit 1; }
project="$(git rev-parse --show-toplevel)"
case "$output/" in "$project/"*) echo 'Output must be outside the checkout.' >&2; exit 1;; esac
cd "$project"
test -f public/build/manifest.json
mkdir -p "$output/app"

# Export only application files; never export a local database, .env or CMS credentials.
git archive HEAD app bootstrap config database/migrations database/seeders database/factories \
    public resources routes artisan composer.json composer.lock .env.staging.example \
    | tar -x -C "$output/app"
cp -R public/build "$output/app/public/build"
for folder in app/private app/public framework/cache/data framework/sessions framework/views logs; do
    mkdir -p "$output/app/storage/$folder"
done
mkdir -p "$output/app/bootstrap/cache"

composer install --working-dir="$output/app" --no-dev --prefer-dist \
    --optimize-autoloader --no-interaction --no-scripts
(
    cd "$output/app"
    php artisan package:discover --no-interaction
    composer check-platform-reqs --no-dev
    php artisan route:list --except-vendor --no-interaction >/dev/null
    git_revision="$(git -C "$project" rev-parse HEAD)"
    printf '%s\n' "$git_revision" > REVISION
    zip -q -r "$output/idlaps-staging.zip" . -x '*.DS_Store' '*.log'
)
unzip -tq "$output/idlaps-staging.zip"
(
    cd "$output"
    shasum -a 256 idlaps-staging.zip > idlaps-staging.zip.sha256
)
echo "Release package: $output/idlaps-staging.zip"
