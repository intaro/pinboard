#!/bin/sh
set -eu

cd /var/www

if [ ! -f vendor/autoload.php ]; then
  echo "vendor/autoload.php not found, running composer install..."
  composer install --no-interaction --prefer-dist
fi

if [ ! -f public/build/manifest.json ] && [ -f package.json ]; then
  echo "Frontend assets are not built (public/build/manifest.json missing)."
  echo "Run 'pnpm install && pnpm build' on host or in a separate node container."
fi

exec "$@"
