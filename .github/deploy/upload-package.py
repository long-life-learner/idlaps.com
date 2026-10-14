"""Upload a release package over explicit FTPS; activation is configured separately."""

import ftplib
import os
import re
import ssl
import sys
from pathlib import Path


def main() -> None:
    package = Path(sys.argv[1])
    if not package.is_file():
        raise SystemExit("Release package is missing.")
    password = os.environ.get("FTP_PASSWORD", "")
    if not password:
        raise SystemExit("Set the FTP_PASSWORD GitHub Actions secret before uploading.")
    release = os.environ["GITHUB_SHA"] + "-" + os.environ["GITHUB_RUN_ID"] + "-" + os.environ["GITHUB_RUN_ATTEMPT"]
    if not re.fullmatch(r"[0-9a-f]{40}-[0-9]+-[0-9]+", release):
        raise SystemExit("Invalid release identifier.")

    with ftplib.FTP_TLS(context=ssl.create_default_context(), timeout=120) as ftp:
        ftp.connect(os.environ["FTP_HOST"], int(os.environ["FTP_PORT"]))
        ftp.login(os.environ["FTP_USERNAME"], password)
        ftp.prot_p()
        ftp.cwd(os.environ["FTP_DIRECTORY"])
        try:
            ftp.cwd("incoming")
        except ftplib.error_perm as error:
            if not str(error).startswith("550"):
                raise
            ftp.mkd("incoming")
            ftp.cwd("incoming")

        # Rename only after upload completes; a partial archive is never presented as ready.
        filename = "idlaps-" + release + ".zip"
        with package.open("rb") as source:
            ftp.storbinary("STOR " + filename + ".part", source, blocksize=262144)
        ftp.voidcmd("TYPE I")
        if ftp.size(filename + ".part") != package.stat().st_size:
            raise SystemExit("Remote upload size does not match the release package.")
        ftp.rename(filename + ".part", filename)
        print("Package uploaded to incoming/" + filename + ". Server activation is still required.")


if __name__ == "__main__":
    main()
