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
- PHP static analysis: `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` — runs at **level 10** (maximum).
- Frontend rebuild: `pnpm build`.
- Before pushing or opening a PR with PHP code changes, run `vendor/bin/php-cs-fixer fix --dry-run --diff` and `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` locally.
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

## Development Workflow

All changes enter `master` exclusively via pull request — direct pushes are blocked.

**Branch → PR → CI → squash merge → master → Release Please → GitHub Release → Docker Hub**

- Branch names: `fix/description`, `feat/description`, `chore/description`, etc.
- Commit titles must follow Conventional Commits (see `docs/contributing.md`).
- Pull request titles must also follow Conventional Commits.
- Do not add extra prefixes to pull request titles, for example `[codex]` or similar tooling markers.
- CI runs three mandatory jobs: `PHP CS Fixer`, `PHPStan`, `PHPUnit (8.4 + 8.5)`.
- After a PR merges, Release Please automatically opens or updates a Release PR.
- Merging the Release PR creates a `vX.Y.Z` tag and GitHub Release.
- The Docker workflow fires on the published release and pushes `xolegator/pinboard:<version>` and `xolegator/pinboard:latest` to Docker Hub.

**Version discipline — choose the commit type by what the change affects, so the version only moves for real app changes.** Application code and the shipped runtime image (`src/**`, `templates/**`, `assets/**`, `migrations/**`, `config/**`, `public/**`, `bin/**`, runtime deps in `composer.json`/`composer.lock` and `package.json`/`pnpm-lock.yaml`, and `Dockerfile.pinboard`) use `feat:`/`fix:` and cut a new version, tag, and Docker image. CI and automation (`.github/**`), docs (`docs/**`, `*.md`), tests and QA config (`tests/**`, `phpstan.neon`, `.php-cs-fixer.dist.php`), and dev-only tooling (`Makefile`, dev-stack `docker/`) use `ci:`/`build:`/`chore:`/`docs:`/`test:`/`style:`/`refactor:` and do **not** trigger a release. Prefer `fix` over `feat` for behaviour corrections. Only `feat`, `fix`, `perf`, `revert`, `deps` and breaking changes are release-triggering. See `docs/releasing.md` → "Version discipline: what warrants a release" for the full rule and reasoning.

Full details: `docs/contributing.md` (PR workflow) and `docs/releasing.md` (release process).

## GitHub Actions

- When adding or editing a workflow, verify the current latest release before
  committing instead of reusing a version from memory or copying an old example,
  e.g.:

  ```bash
  gh api repos/docker/build-push-action/releases/latest --jq .tag_name
  ```

- Reference first-party GitHub actions by their major tag (e.g.
  `actions/checkout@v7`) so they track the latest compatible patch.
- Reference third-party actions by full commit SHA and add the verified release
  tag as a trailing comment, e.g.
  `docker/build-push-action@53b7df96c91f9c12dcc8a07bcb9ccacbed38856a # v7.3.0`.
  This is required to keep CodeQL `actions/unpinned-tag` clean. Resolve annotated
  tags to the target commit SHA, not the tag object SHA.
- Never introduce a version older than what the rest of the repository (or the
  sibling Pinba repositories) already uses.
- Dependabot (`.github/dependabot.yml`) opens grouped `ci:` PRs for action bumps;
  keep all workflows (`ci.yml`, `codeql.yml`, `docker.yml`, `release-please.yml`)
  on the same current majors.

## Dependency Updates

**General Rules:**
- Update dependencies only to stable, released versions (no `-beta`, `-rc`, `-dev`).
- Updates are always **forward** to newer stable versions; backtracking to older versions is forbidden without explicit justification in the commit message.
- Pinned hashes (Docker images, GitHub Actions) must be updated to reflect the new version; never remove a hash that already exists.
- Group related dependency updates into a single consolidated commit when possible (e.g., all PHP deps, all JS deps, all Docker images).
- All dependency updates must be done in a feature branch (`chore/update-*` naming) and submitted via PR.

**PHP Dependencies (composer.json / composer.lock):**
- Run `composer update` to get the latest compatible versions within declared constraints.
- Pin Symfony to LTS versions when available (e.g., `8.1.*`); never jump minor versions without review.
- Merge all package updates into one commit, not one per package.

**JavaScript Dependencies (package.json / pnpm-lock.yaml):**
- Run `pnpm update` to update all dependencies within version constraints.
- Use the pinned `pnpm` version from `package.json` `packageManager` field; do not manually change it.
- Update `.nvmrc` only if the Node version constraint in `package.json` changes or there is a security reason.

**Docker Base Images (Dockerfile.pinboard):**
- Always pin Docker base images to their full digest (SHA256 hash), never use untagged `latest`.
- When updating an image tag (e.g., `node:24-alpine`), fetch the current digest via `docker pull <image>` and update the hash.
- Example: `FROM node:24-alpine@sha256:e67514e5d0f6c46656005e1b693b2ec9d52e80b641307de684d4a015ba7a4eaf`
- Never remove an existing hash; always replace it with the new one if upgrading.

**GitHub Actions (.github/workflows/*.yml):**
- First-party GitHub actions (e.g., `actions/checkout`, `actions/setup-node`) use major version tags (`v7`, `v4`), which auto-track latest compatible patches.
- Third-party actions (e.g., `docker/build-push-action`, `shivammathur/setup-php`) must be pinned by full commit SHA with the release tag as a comment.
- To find the SHA: `gh api repos/<owner>/<repo>/git/ref/tags/<tag> --jq .object.sha` or check the GitHub release page.
- Example: `uses: docker/build-push-action@10e90e3645eae34f1e60eeb005ba3a3d33f178e8 # v6`
- Never downgrade action major versions (e.g., v7 → v4); this is a backwards step and violates the forward-only rule.
- When Dependabot opens grouped PRs for action updates, validate they are actual upgrades before merging.

**Commit Message Template for Dependency Updates:**
```
chore: update <layer> dependencies to latest stable versions

- <package>: <old> → <new> (X updates total)
- <package>: <old> → <new>

All versions are stable releases. GitHub Actions already at latest majors.

Closes: #<PR> #<PR>
```

## Commit Messages

- Use Conventional Commits style.
- Keep commit titles short, imperative, and scoped when useful.
- Examples that fit this project:
  - `fix(ui): correct timer table layout`
  - `feat(auth): restore db-backed user sync`
  - `refactor(config): modernize sass build`
- Match the existing repository style: concise English messages with an optional scope.
