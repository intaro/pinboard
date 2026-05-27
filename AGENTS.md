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
- PHP unit and functional tests: `vendor/bin/phpunit`.
- PHP style: `vendor/bin/php-cs-fixer fix --dry-run --diff` — fix violations with `vendor/bin/php-cs-fixer fix`.
- PHP static analysis: `vendor/bin/phpstan analyse --no-progress` — runs at **level 10** (maximum).
- Frontend rebuild: `pnpm build`.
- Prefer focused verification over broad test runs unless the change spans multiple layers.

## PHP Static Analysis Rules

PHPStan runs at level 10. All violations must be fixed with real engineering solutions.

**Forbidden shortcuts:**
- No `@phpstan-ignore` or `@phpstan-ignore-next-line` comments.
- No inline `@var` PHPDoc to override inferred types.
- No `assert()` to satisfy the type checker.
- No widening parameter or return types just to silence an error.
- No casts (`(string)`, `(float)`, `(int)`) applied directly to `mixed` — they are not allowed at level 10.

**Correct patterns:**
- Narrow `mixed` with guards before use: `is_string($x)`, `is_numeric($x)`, `is_array($x)`.
- At DB boundaries (`fetchAllAssociative()`, `fetchOne()`), extract typed locals from each row explicitly.
- Use `fetchOne()` for scalar queries (COUNT, single-column); never index `fetchAllAssociative()[0]['col']`.
- Declare PHP 8.3 typed class constants (`public const int X = 1`) to prevent `static::CONST` resolving to `mixed`.
- Annotate repository methods with typed PHPDoc array shapes, e.g. `@return list<array{id: int, name: string}>`, and cast at the boundary.
- For `Yaml::parseFile()` and session `get()` returning `mixed`, validate with `is_array()` and iterate to enforce string keys.

## Commit Messages

- Use Conventional Commits style.
- Keep commit titles short, imperative, and scoped when useful.
- Examples that fit this project:
  - `fix(ui): correct timer table layout`
  - `feat(auth): restore db-backed user sync`
  - `refactor(config): modernize sass build`
- Match the existing repository style: concise English messages with an optional scope.
