"""Integration checks against the exact production release archive."""

import base64
import hashlib
import importlib.util
import json
import os
from pathlib import Path
import shutil
import sqlite3
import subprocess
import sys
import tempfile
import unittest
import zipfile

sys.dont_write_bytecode = True
PACKAGE = Path(sys.argv.pop(1)).resolve()
DEPLOY = Path(__file__).resolve().parent


class ReleaseActivationTest(unittest.TestCase):
    def test_activation_preserves_cms_data_and_rejects_corrupt_packages(self):
        with tempfile.TemporaryDirectory(prefix='idlaps-deploy-test-') as directory:
            base = Path(directory).resolve()
            (base / 'incoming').mkdir()
            (base / 'public').mkdir()
            shutil.copyfile(DEPLOY / 'activate-release.php', base / 'activate-release.php')
            database = base / 'database.sqlite'
            database.touch()
            environment = '\n'.join([
                'APP_ENV=production', 'APP_DEBUG=false',
                'APP_KEY=base64:' + base64.b64encode(b'test-key-only-00000000000000000000'[:32]).decode(),
                'APP_URL=https://staging.idlaps.com', 'DB_CONNECTION=sqlite',
                'DB_DATABASE=' + str(database), 'SESSION_DRIVER=database',
                'CACHE_STORE=database', 'QUEUE_CONNECTION=sync', 'SITE_INDEXABLE=false', '',
            ])
            (base / '.env').write_text(environment)
            with zipfile.ZipFile(PACKAGE) as archive:
                revision = archive.read('REVISION').decode().strip()
            checksum = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()

            def activate(attempt, digest=checksum):
                release = revision + '-1-' + str(attempt)
                shutil.copyfile(PACKAGE, base / 'incoming' / ('idlaps-' + release + '.zip'))
                (base / 'incoming/pending.json').write_text(json.dumps({'release': release, 'sha256': digest}))
                command_environment = {key: value for key, value in os.environ.items() if not key.startswith(('APP_', 'DB_', 'CACHE_', 'SESSION_', 'QUEUE_'))}
                result = subprocess.run(['php', str(base / 'activate-release.php')], capture_output=True, text=True, env=command_environment)
                return result, release

            result, first = activate(1)
            self.assertEqual(result.returncode, 0, result.stderr + ((base / 'deploy-error.log').read_text() if (base / 'deploy-error.log').exists() else ''))
            self.assertEqual((base / 'public').resolve(), base / 'releases' / first / 'public')
            credentials = (base / 'storage/app/private/cms-login.txt').read_bytes()
            with sqlite3.connect(database) as connection:
                self.assertGreater(connection.execute('SELECT COUNT(*) FROM events').fetchone()[0], 0)
                self.assertEqual(connection.execute('SELECT COUNT(*) FROM users WHERE is_admin=1').fetchone()[0], 1)
                connection.execute("UPDATE events SET name='CMS edited event' WHERE id=(SELECT MIN(id) FROM events)")
            photo = base / 'storage/app/private/cms-photo.txt'
            photo.write_text('existing upload')

            result, second = activate(2)
            self.assertEqual(result.returncode, 0, result.stderr)
            self.assertEqual((base / 'public').resolve(), base / 'releases' / second / 'public')
            self.assertTrue((base / 'releases' / first).is_dir())
            self.assertEqual(photo.read_text(), 'existing upload')
            self.assertEqual((base / '.env').read_text(), environment)
            self.assertEqual((base / 'storage/app/private/cms-login.txt').read_bytes(), credentials)
            with sqlite3.connect(database) as connection:
                self.assertEqual(connection.execute("SELECT COUNT(*) FROM events WHERE name='CMS edited event'").fetchone()[0], 1)

            result, _ = activate(3, '0' * 64)
            self.assertNotEqual(result.returncode, 0)
            self.assertEqual((base / 'public').resolve(), base / 'releases' / second / 'public')
            self.assertEqual(json.loads((base / 'incoming/result.json').read_text())['status'], 'failed')
            self.assertFalse((base / 'incoming/pending.json').exists())

            malicious_release = revision + '-1-4'
            malicious_archive = base / 'incoming' / ('idlaps-' + malicious_release + '.zip')
            with zipfile.ZipFile(malicious_archive, 'w') as archive:
                archive.writestr('../outside.txt', 'must not extract')
            (base / 'incoming/pending.json').write_text(json.dumps({'release': malicious_release, 'sha256': hashlib.sha256(malicious_archive.read_bytes()).hexdigest()}))
            rejected = subprocess.run(['php', str(base / 'activate-release.php')], capture_output=True, text=True)
            self.assertNotEqual(rejected.returncode, 0)
            self.assertFalse((base / 'releases/outside.txt').exists())
            self.assertEqual((base / 'public').resolve(), base / 'releases' / second / 'public')

            spec = importlib.util.spec_from_file_location('upload', DEPLOY / 'upload-package.py')
            upload = importlib.util.module_from_spec(spec)
            spec.loader.exec_module(upload)
            special_password = 'space #quote"slash\\ dollar${HOME} apostrophe\' end'
            parsed = subprocess.run(['php', '-r', 'require $argv[1]; echo json_encode(Dotenv\\Dotenv::parse($argv[2]));', str(base / 'releases' / second / 'vendor/autoload.php'), 'DB_PASSWORD=' + upload.dotenv_value(special_password)], capture_output=True, text=True, check=True)
            self.assertEqual(json.loads(parsed.stdout)['DB_PASSWORD'], special_password)


if __name__ == '__main__':
    unittest.main()
