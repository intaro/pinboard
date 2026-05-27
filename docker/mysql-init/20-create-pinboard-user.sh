#!/bin/bash
set -euo pipefail

if [[ -z "${DB_APP_USER:-}" || -z "${DB_APP_PASSWORD:-}" || -z "${MYSQL_DATABASE:-}" ]]; then
  echo "Skip pinboard DB user creation: required env vars are not set"
  exit 0
fi

export MYSQL_PWD="${MYSQL_ROOT_PASSWORD}"
mysql -uroot <<SQL
CREATE USER IF NOT EXISTS '${DB_APP_USER}'@'%' IDENTIFIED BY '${DB_APP_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${DB_APP_USER}'@'%';
FLUSH PRIVILEGES;
SQL

echo "Pinboard DB user '${DB_APP_USER}' ensured for database '${MYSQL_DATABASE}'."
