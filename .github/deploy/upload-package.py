"""Deliver a verified release and wait for the private cPanel CLI activator."""

import base64
import ftplib
import hashlib
import io
import json
import os
import re
import ssl
import sys
import time
from pathlib import Path


def dotenv_value(value: str) -> str:
    return '"' + value.replace('\\', '\\\\').replace('"', '\\"').replace('$', '\\$') + '"'


def upload(ftp, name: str, content: bytes, private: bool = False) -> None:
    ftp.storbinary('STOR ' + name + '.part', io.BytesIO(content), blocksize=262144)
    ftp.voidcmd('TYPE I')
    if ftp.size(name + '.part') != len(content):
        raise RuntimeError('Uploaded file size mismatch: ' + name)
    if private:
        ftp.sendcmd('SITE CHMOD 600 ' + name + '.part')
    ftp.rename(name + '.part', name)


def main() -> None:
    package = Path(sys.argv[1])
    password = os.environ.get('FTP_PASSWORD', '')
    database_password = os.environ.get('STAGING_DB_PASSWORD', '')
    if not package.is_file() or not password or not database_password:
        raise SystemExit('Package, FTP_PASSWORD and STAGING_DB_PASSWORD are required.')
    release = os.environ['GITHUB_SHA'] + '-' + os.environ['GITHUB_RUN_ID'] + '-' + os.environ['GITHUB_RUN_ATTEMPT']
    if not re.fullmatch(r'[0-9a-f]{40}-[0-9]+-[0-9]+', release):
        raise SystemExit('Invalid release identifier.')
    with ftplib.FTP_TLS(context=ssl.create_default_context(), timeout=120) as ftp:
        ftp.connect(os.environ['FTP_HOST'], int(os.environ['FTP_PORT']))
        ftp.login(os.environ['FTP_USERNAME'], password)
        ftp.prot_p()
        ftp.cwd(os.environ['FTP_DIRECTORY'])
        names = ftp.nlst()
        if 'incoming' not in [name.rstrip('/').split('/')[-1] for name in names]:
            ftp.mkd('incoming')
        ftp.voidcmd('TYPE I')
        try:
            ftp.size('.env')
        except ftplib.error_perm as error:
            if not str(error).startswith('550'):
                raise
            template = Path('.env.staging.example').read_text()
            key = 'base64:' + base64.b64encode(os.urandom(32)).decode()
            template = template.replace('APP_KEY=\n', 'APP_KEY=' + key + '\n')
            template = template.replace('DB_PASSWORD=\n', 'DB_PASSWORD=' + dotenv_value(database_password) + '\n')
            upload(ftp, '.env', template.encode(), private=True)
        upload(ftp, 'activate-release.php', Path('.github/deploy/activate-release.php').read_bytes())
        content = package.read_bytes()
        upload(ftp, 'incoming/idlaps-' + release + '.zip', content)
        manifest = json.dumps({'release': release, 'sha256': hashlib.sha256(content).hexdigest()}).encode()
        upload(ftp, 'incoming/pending.json', manifest, private=True)
        print('Release uploaded. Waiting for the cPanel cron to activate it.', flush=True)
        deadline = time.monotonic() + 480
        while time.monotonic() < deadline:
            result_file = io.BytesIO()
            try:
                ftp.retrbinary('RETR incoming/result.json', result_file.write)
                result = json.loads(result_file.getvalue())
                if result.get('release') == release:
                    if result.get('status') != 'success':
                        raise SystemExit('Activation failed. Inspect the private deploy-error.log in cPanel.')
                    print('Release activated successfully on PHP ' + result['php'] + ': ' + release)
                    return
            except ftplib.error_perm as error:
                if not str(error).startswith('550'):
                    raise
            except json.JSONDecodeError:
                pass
            time.sleep(10)
        raise SystemExit('Activation timed out. Verify the cPanel cron command and private deployment logs.')


if __name__ == '__main__':
    main()
