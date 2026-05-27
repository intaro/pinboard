# Testing

## Code checks

- PHP syntax: `php -l path/to/file.php`
- Twig syntax: `php bin/console lint:twig templates/...`
- Frontend build: `pnpm build`
- Static analysis: `vendor/bin/phpstan analyse`
- Code style: `vendor/bin/php-cs-fixer fix --dry-run`

## What to verify after changes

- Templates and layout — in a browser.
- Sass and JS — after rebuilding the frontend.
- Symfony Console commands — on a real local environment.
- Pinba data changes — on admin pages and in the database.

## Practices

- For targeted changes, run only the checks relevant to the modified files.
- If a change touches the shared layout or build pipeline, rebuild the frontend and open key pages manually.
