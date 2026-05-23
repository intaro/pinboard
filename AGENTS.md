# AGENTS.md

## Project Shape

- Symfony 8 admin for Pinba.
- Frontend assets are built with Symfony Encore.
- Local configuration lives in `.env.local`; never commit secrets from it.
- Use `pnpm` for frontend dependency management.

## Working Rules

- Prefer the existing project patterns over new abstractions.
- Keep changes scoped to the requested behavior.
- If you change Sass, Twig, or JS assets, rebuild the frontend before finishing.
- Do not revert user changes that are already present in the worktree.

## Runtime Notes

- Use the environment's configured PHP binary when running console commands.
- For background jobs, use the service account that owns the local site data in that environment.
- The important console commands are `aggregate`, `register-crontab`, and the user migration commands documented in `README.md`.

## Testing

- Backend sanity checks: `php -l` for touched PHP files and `php bin/console lint:twig` for touched Twig files.
- PHP quality checks: `vendor/bin/phpunit`, `vendor/bin/phpstan analyse -c phpstan.neon`, `vendor/bin/php-cs-fixer fix --dry-run --diff --sequential --using-cache=no`.
- Frontend rebuild: `pnpm build`.
- Prefer focused verification over broad test runs unless the change spans multiple layers.

## Commit Messages

- Use Conventional Commits style.
- Keep commit titles short, imperative, and scoped when useful.
- Examples that fit this project:
  - `fix(ui): correct timer table layout`
  - `feat(auth): restore db-backed user sync`
  - `refactor(config): modernize sass build`
- Match the existing repository style: concise English messages with an optional scope.
