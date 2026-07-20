# Testing

## Isolated Docker test stack

Use the local test stack for the closest developer-machine equivalent of CI:

```bash
make test
```

This command uses `docker/.env.test` and the Compose project name `pinboard-test`.
It starts only the test MySQL/Pinba, PHP-FPM and Node containers, installs PHP
and frontend dependencies, runs Doctrine migrations in the test database, then
runs:

- `php bin/console lint:twig`
- `vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no`
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
- `vendor/bin/phpunit --no-progress`
- `pnpm build`

The stack is independent from the development stack (`pinboard`) and uses
auto-assigned host ports to avoid conflicts. It is left running after the check
so repeat runs can reuse containers and dependency caches.

```bash
make test-down   # stop only the pinboard-test containers
make test-reset  # stop them and remove only the pinboard-test volumes
```

## Local checks before pushing

Run these before every push to avoid CI failures:

```bash
# PHP syntax (touched files only)
php -l src/Path/To/File.php

# Twig syntax
php bin/console lint:twig

# Code style — check, then fix
vendor/bin/php-cs-fixer fix --dry-run --diff
vendor/bin/php-cs-fixer fix

# Static analysis (level 10)
vendor/bin/phpstan analyse --no-progress

# Unit and functional tests
vendor/bin/phpunit --no-progress

# Frontend assets
pnpm build
```

## CI pipeline

Three jobs run automatically on every PR and on every push to `master`:

| Job            | Command                                         |
|----------------|-------------------------------------------------|
| PHP CS Fixer   | `vendor/bin/php-cs-fixer fix --dry-run --diff`  |
| PHPStan        | `vendor/bin/phpstan analyse --no-progress`       |
| PHPUnit        | `vendor/bin/phpunit --no-progress` (PHP 8.4 + 8.5) |

All three must pass before a PR can be merged. See `.github/workflows/ci.yml` for the full configuration.

## What to verify after changes

- Templates and layout — open the relevant pages in a browser.
- Sass and JS — after rebuilding with `pnpm build`.
- Symfony Console commands — run on a real local environment.
- Pinba data changes — verify on admin pages and in the database.

## Practices

- For targeted changes, run only the checks relevant to the modified files.
- If a change touches the shared layout or build pipeline, rebuild the frontend and open key pages manually.
- PHPStan runs at level 10. All violations must be fixed with real solutions — see `AGENTS.md` for the full list of allowed and forbidden patterns.
