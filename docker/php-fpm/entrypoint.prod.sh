#!/bin/bash
set -euo pipefail

DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:-pinba}"
DB_USER="${DB_USER:-pinba}"
DB_PASSWORD="${DB_PASSWORD:-pinba}"

# ── Wait for MySQL ────────────────────────────────────────────────────────────
# Uses PHP PDO instead of mysqladmin to avoid MariaDB client / caching_sha2_password
# incompatibility when running against MySQL 8.x on Alpine.
echo "[pinboard] Waiting for database at ${DB_HOST}:${DB_PORT}..."
retries=30
until php -r "
try {
    new PDO(
        'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_NAME}',
        '${DB_USER}',
        '${DB_PASSWORD}',
        [PDO::ATTR_TIMEOUT => 3]
    );
} catch (Exception \$e) { exit(1); }
" 2>/dev/null; do
    retries=$((retries - 1))
    if [ "${retries}" -le 0 ]; then
        echo "[pinboard] ERROR: Database not available after 60s."
        echo "[pinboard]   DB_HOST=${DB_HOST}  DB_PORT=${DB_PORT}  DB_USER=${DB_USER}"
        exit 1
    fi
    echo "[pinboard] Retrying... (${retries} left)"
    sleep 2
done
echo "[pinboard] Database is ready."

# ── Run migrations (web container only — aggregate shares the same DB) ───────
if [ "${1:-}" = "/usr/bin/supervisord" ]; then
    echo "[pinboard] Running database migrations..."
    su -s /bin/sh www-data -c "php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration"
fi

# ── Warm cache (web container only) ──────────────────────────────────────────
# The cache lives on the ephemeral container filesystem (no volume), so a
# recreated container always starts with a cache compiled from its own image —
# never a stale cache from a previous version.
if [ "${1:-}" = "/usr/bin/supervisord" ] && [ ! -d var/cache/prod ]; then
    echo "[pinboard] Warming up Symfony cache..."
    su -s /bin/sh www-data -c "php bin/console cache:warmup"
fi

# ── First-run hint ────────────────────────────────────────────────────────────
if [ "${APP_AUTH_USER_SOURCE:-db}" = "db" ] && [ "${1:-}" = "/usr/bin/supervisord" ]; then
    echo ""
    echo "┌─────────────────────────────────────────────────────┐"
    echo "│  First run? Create an admin user:                   │"
    echo "│                                                      │"
    echo "│  docker exec pinboard-web \\                         │"
    echo "│    php bin/console add-user \\                       │"
    echo "│    admin@example.com yourpassword ROLE_ADMIN         │"
    echo "└─────────────────────────────────────────────────────┘"
    echo ""
fi

exec "$@"
