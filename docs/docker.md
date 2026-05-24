# Docker: Pinboard + Pinba

Two ways to run the stack:

| | [Option A — Public images](#option-a-public-images-quick-start) | [Option B — Dev stack](#option-b-dev-stack-source-build) |
|---|---|---|
| **Use case** | Try it out / production deployment | Contribute to Pinboard |
| **Images** | Pre-built from Docker Hub | Built from local source |
| **PHP Pinba traffic** | from real PHP apps | from real PHP apps |
| **Config** | `.env` (from `.env.public.example`) | `docker/.env` |
| **Start command** | `docker compose -f docker-compose.public.yml up -d` | `make up` |

---

## Option A — Public images (quick-start)

Uses pre-built images from Docker Hub:
- `xolegator/pinba-engine:8.4-lts` — MySQL 8.4 LTS with Pinba storage engine
- `xolegator/pinboard:latest` — Pinboard web app + aggregate worker

### Step 1 — Configure

```bash
cp .env.public.example .env
```

Open `.env` and set the three required values:

```env
APP_SECRET=<output of: openssl rand -hex 32>
MYSQL_ROOT_PASSWORD=<strong password>
DB_PASSWORD=<strong password>
```

Everything else has sensible defaults. Key optional overrides:

```env
PINBOARD_HTTP_PORT=8080     # web UI port on your host
PINBA_UDP_PORT=30002        # UDP port PHP apps send Pinba packets to
TZ=UTC                      # timezone for aggregation timestamps
APP_RECORDS_LIFETIME=P1M    # how long to keep raw data (ISO 8601 duration)
```

### Step 2 — Start

```bash
docker compose -f docker-compose.public.yml up -d
```

Three containers start:
- `pinboard-pinba-db` — MySQL 8.4 + Pinba engine (UDP listener + data store)
- `pinboard-web` — nginx + php-fpm serving the Pinboard UI
- `pinboard-aggregate` — supercronic running `aggregate` every 15 minutes

The web container waits for the DB healthcheck to pass, then runs Doctrine migrations automatically on first boot.

### Step 3 — Create admin user

```bash
docker exec pinboard-web php bin/console add-user admin@example.com yourpassword ROLE_ADMIN
```

Open **http://localhost:8080** (or your `PINBOARD_HTTP_PORT`) and log in.

### Step 4 — Point PHP apps at Pinba

In `php.ini` or your FPM pool config on each monitored server:

```ini
pinba.enabled = 1
pinba.server  = <docker-host-ip>:30002
```

Restart PHP-FPM. Data appears in Pinboard in two stages — see [Data visibility timeline](deployment.md#data-visibility-timeline) for details. To trigger the first aggregation immediately instead of waiting 15 minutes:

```bash
docker exec pinboard-aggregate php bin/console aggregate --no-interaction
```

### Diagnostics

```bash
# Check all three containers are healthy
docker compose -f docker-compose.public.yml ps

# View web container startup logs (migrations, cache warmup)
docker logs pinboard-web

# Verify Pinba plugin is active
docker exec pinboard-pinba-db mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" \
  -e "SHOW PLUGINS LIKE 'pinba';"

# Count raw requests in the Pinba in-memory table
docker exec pinboard-pinba-db mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" \
  -D pinba -e "SELECT COUNT(*) FROM request;"

# View aggregation cron logs
docker logs pinboard-aggregate

# Force an aggregation run now
docker exec pinboard-aggregate php bin/console aggregate --no-interaction
```

### Upgrading

```bash
docker compose -f docker-compose.public.yml pull
docker compose -f docker-compose.public.yml up -d
```

Migrations run automatically on container start.

### Stop / remove

```bash
# Stop (keep data)
docker compose -f docker-compose.public.yml down

# Stop and wipe all data (volumes)
docker compose -f docker-compose.public.yml down -v
```

---

## Option B — Dev stack (source build)

For Pinboard contributors. Mounts source code into containers so edits are reflected immediately (no image rebuild required).

### Architecture

- **`mysql-pinba`** — MySQL + Pinba engine (from `xolegator/pinba-engine:8.4-lts` by default)
- **`php-fpm`** — PHP-FPM with source mounted at `/var/www`
- **`nginx`** — proxies HTTP to php-fpm
- **`aggregate`** — runs `php bin/console aggregate` on cron

### MySQL version variants

| Env file | Image | Make targets |
|---|---|---|
| `docker/.env` (default) | `xolegator/pinba-engine:8.4-lts` | `make up`, `make build` |
| `docker/.env.mysql84` | `xolegator/pinba-engine:8.4-lts` | `make up84`, `make build84` |
| `docker/.env.mysql80` | `xolegator/pinba-engine:8.0` | `make up80`, `make build80` |

### Start

```bash
# Build local images and start (MySQL 8.4 LTS by default)
make build
make up

# Or explicitly with MySQL 8.0 (for compatibility testing):
make build80
make up80
```

### First-time init

```bash
make db_migrate

# Create a user
docker compose --env-file ./docker/.env -f ./docker/docker-compose.yml \
  exec -u www-data php-fpm php bin/console add-user admin@example.com yourpassword ROLE_ADMIN
```

Open **http://localhost:18088** (or `NGINX_HOST_HTTP_PORT` from `docker/.env`).

### Common dev commands

```bash
make up           # start stack
make down         # stop stack
make dc_logs      # tail all logs
make dc_ps        # show container status
make db_migrate   # run Doctrine migrations
make test         # run PHPUnit
make phpstan      # run static analysis
make cs_fix       # run PHP-CS-Fixer
make app_bash     # open shell in php-fpm container
```

### Sending Pinba traffic in dev

Configure your local PHP to send to the UDP port from `docker/.env` (`PINBA_UDP_PORT`, default `31002`):

```ini
pinba.enabled = 1
pinba.server  = 127.0.0.1:31002
```

### Building the public image locally

To build `Dockerfile.pinboard` (the production single-container image):

```bash
docker build -t xolegator/pinboard:latest -f Dockerfile.pinboard .
```

See comments in `Dockerfile.pinboard` for the three build stages (node → composer → php-fpm+nginx).
