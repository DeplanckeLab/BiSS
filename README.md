# BiSS
Yeast one-hybrid screening for DNA-protein interactions

# TF motif database
The Transcription Factor database used is for *Drosophila melanogaster*, it consists of a file with 3900 [:page_facing_up: PWM motifs](https://github.com/DeplanckeLab/BiSS/blob/main/db/dmel_only.meme-io) annotating 838 unique Transcription Factors. A more precise annotation of these TFs can be find in this [:page_facing_up: annotation file](https://github.com/DeplanckeLab/BiSS/blob/main/db/annotations.tsv).

This database was created by aggregating data from the [Fly Factor Survey](https://mccb.umassmed.edu/ffs/), [JASPAR](https://jaspar.genereg.net/search?q=&collection=CORE&tax_group=insects&tax_id=7227), [Transfac](http://gene-regulation.com/pub/databases.html#transfac), hdpi, [YeTFaSCo](http://yetfasco.ccbr.utoronto.ca/), consensus data from [SeleX](https://pubmed.ncbi.nlm.nih.gov/19231809/), data from [Elemento & Tavazoie](https://genomebiology.biomedcentral.com/articles/10.1186/gb-2005-6-2-r18), as well as data from [Stark lab](https://starklab.org/data/index.html).

# Website
This is the source code of our interactive website BiSS, which is available at :globe_with_meridians:[https://biss.epfl.ch/](https://biss.epfl.ch/)

# How do we get the transcription factors ?
We use :computer:[FIMO](https://meme-suite.org/meme/doc/fimo.html) to screen, and then annotate its output by adding a **binding site** column, those being found by querying our *Drosophila melanogaster* TF motif database (see above)

# What sequence type can be submitted ?
Input sequences should be in FASTA format. You can input one sequence without header, and it will be named **user input**, but it has still to be in FASTA format

# Running BiSS with Docker

The whole website runs in Docker, with everything it needs inside the image:

| Component | Version | Used for |
|---|---|---|
| PHP + Apache (`php:8.5.12-apache-trixie`) | PHP 8.5.12 | The website |
| [MEME Suite](https://meme-suite.org/) (built from source) | 5.5.9 | `fimo`, the motif scanner (`/opt/meme/bin/fimo`) |
| Python | 3.13 | `annotate.py`, which adds TF names to the FIMO output |

`docker compose` starts two containers from the same image:

- **web**: the website, on port 80 by default.
- **cleanup**: deletes runs older than 7 days (configurable). It replaces the old `cron_script.sh`.

User runs and sessions are stored in the Docker volume `biss-data`, outside the web root. They are temporary by design, so they don't need backups.

## 1. Install Docker on the VM

Follow the official guide for your Linux distribution: <https://docs.docker.com/engine/install/>. It installs Docker Engine and the `docker compose` plugin. For example, on Ubuntu or Debian:

```bash
curl -fsSL https://get.docker.com | sudo sh
```

Then make Docker start at boot, and allow your user to run it without `sudo` (log out and back in afterwards):

```bash
sudo systemctl enable --now docker
sudo usermod -aG docker "$USER"
```

Check that it works:

```bash
docker run --rm hello-world
docker compose version
```

## 2. Start BiSS

```bash
git clone https://github.com/DeplanckeLab/BiSS.git
cd BiSS
docker compose up -d --build
```

The first build takes a few minutes, mostly to compile MEME. Then open `http://<address-of-the-VM>/` in a browser.

Check that both containers are running and that `web` is `healthy` (about one minute after start):

```bash
docker compose ps
```

### Running permanently

Nothing else is needed. Both containers use `restart: unless-stopped`, and Docker starts at boot (`systemctl enable docker` above). So BiSS restarts by itself after a crash or a reboot of the VM. It only stays down if you stop it with `docker compose stop` or `docker compose down`.

## 3. Configuration (optional)

To change a setting, create a file named `.env` next to `docker-compose.yml`, then apply it with `docker compose up -d`:

```bash
# Port of the website on the VM (default: 80).
# Use 127.0.0.1:8080 to only allow access from the VM itself (e.g. behind a reverse proxy).
BISS_PORT=80
# Number of days without a new submission after which a user's runs are deleted (default: 7)
BISS_RETENTION_DAYS=7
# How often (in seconds) the cleanup runs (default: 3600)
BISS_CLEANUP_INTERVAL=3600
```

## 4. HTTPS (optional)

The container serves plain HTTP. For HTTPS, put a reverse proxy in front of it: your institution's proxy, or [Caddy](https://caddyserver.com/), which gets and renews certificates automatically. Caddy needs the domain's DNS to point to the VM and ports 80 and 443 to be reachable from the internet.

1. In `.env`, set `BISS_PORT=127.0.0.1:8080`, so that only Caddy can reach the website.
2. Create `docker-compose.override.yml` next to `docker-compose.yml`, replacing `biss.epfl.ch` with your domain:

   ```yaml
   services:
     caddy:
       image: caddy:2
       restart: unless-stopped
       ports:
         - "80:80"
         - "443:443"
       command: caddy reverse-proxy --from biss.epfl.ch --to web:80
       volumes:
         - caddy-data:/data
   volumes:
     caddy-data:
   ```

3. Run `docker compose up -d`. Docker Compose reads the override file automatically.

## Everyday commands

Run these from the `BiSS` folder:

| Task | Command |
|---|---|
| Status | `docker compose ps` |
| Logs (Apache, PHP errors, cleanup) | `docker compose logs -f` |
| Stop / start | `docker compose stop` / `docker compose start` |
| Update after a `git pull`, or to get security updates | `docker compose build --pull && docker compose up -d` |
| Delete all user runs now | `docker compose exec -e BISS_RETENTION_DAYS=0 cleanup biss-cleanup` |
| Check the tools inside the image | `docker compose exec web sh -c 'php -v; /opt/meme/bin/fimo --version; python3 --version'` |

Rebuilding with `--pull` picks up the latest Debian and PHP image security patches. To move to a newer PHP release, change `PHP_VERSION` at the top of the `Dockerfile` and rebuild.

## Troubleshooting

- **`port is already allocated`**: another program uses port 80 on the VM. Set another port in `.env` (e.g. `BISS_PORT=8080`) and open `http://<address-of-the-VM>:8080/`.
- **A run shows `Error`**: open its log file from the "View runs" page, and check `docker compose logs web`.
- **Lost access to earlier runs**: runs belong to the browser session that submitted them (a session cookie). Closing the browser, or not submitting anything for `BISS_RETENTION_DAYS` days, loses them.

## Security

The image fixes the "php2shell" vulnerabilities [CVE-2026-109618 and CVE-2026-109619](https://calif.io/php2shell/), remote code execution in PHP. The fix is in PHP 8.5.12, released on 2026-10-21. The build refuses to use an older PHP. The image also applies the advisory's mitigations, as extra protection:

- `session.upload_progress.enabled = 0` and `fatal_error_backtraces = 0`
- Small request limits (`post_max_size = 8M`, plus Apache's `LimitRequestBody`)
- Apache refuses chunked request bodies and only speaks HTTP/1.1. These are the two ways the attack gets around `post_max_size`.

The upgrade from PHP 5 also fixed several application vulnerabilities:

- Reading any file on the server through the `job` parameter (path traversal)
- Unescaped shell arguments when starting FIMO
- Cross-site scripting through sequence names, file names and error messages
- Session fixation (`session.use_strict_mode`)

The site runs with a hardened PHP configuration (`docker/php.ini`) and Apache configuration (`docker/apache-biss.conf`).

## Running without Docker

You need Apache with PHP >= 8.5.12, MEME Suite >= 5 (`fimo` in `/opt/meme/bin`, or set `FIMO_BIN`) and `python3`. User data goes to `tmp/` in the website folder, or to the folder set in `BISS_JOBS_DIR`. It must be writable by the web server. The settings in `docker/php.ini` and `docker/apache-biss.conf` are recommended.
