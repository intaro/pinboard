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
- Git-ignored local files are irreplaceable user data, not build output: `.env.local`, `.env.public.local`, `config/parameters.yml` (file-based users), `.idea/`, local tool binaries. They exist in no commit and no backup.
- Never use `git reset --hard`, `git clean`, `git checkout -- .` or `git stash -u/-a` as a "try, then revert" loop in the main checkout. Trial runs (e.g. `composer recipes:update`) go into a throwaway worktree: `git worktree add <tmp>/wt HEAD`, then `composer install` there, then `git worktree remove`.
  - Why: tools can rewrite `.gitignore` (Flex recipes do). A later `git add -A`/`-N` then picks up formerly ignored files, and `reset --hard` deletes them from disk. In 2026-09 this wiped `.env.local` (lost for good), `config/parameters.yml`, `.idea/`, `vendor/`, `node_modules/` and `var/`.
  - Before any command that may delete untracked or ignored files, preview it (`git clean -n -d`, `git status --ignored`) and check that `.gitignore` is unchanged.

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

Step-by-step runbook (inventory commands, where every version lives, verification matrix, known
pitfalls): [`docs/dependency-updates.md`](docs/dependency-updates.md). Follow it for scheduled bulk
updates; the rules below are binding.

**General Rules:**
- Update dependencies only to stable, released versions (no `-beta`, `-rc`, `-dev`), and skip releases younger than 24h (same window as pnpm `minimumReleaseAge`).
- Updates are always **forward** to newer stable versions; backtracking to older versions is forbidden without explicit justification in the commit message.
- Major upgrades (including majors of transitive packages and of tooling) are allowed, but only after reading the package's `UPGRADE.md` / release notes and grepping the code for every affected API — never guess.
- Pinned hashes (Docker images, GitHub Actions, supercronic) must be updated to reflect the new version; never remove a hash that already exists.
- Take a baseline of all checks before updating, so pre-existing failures are not reported as regressions.
- Group related dependency updates into one commit per layer (PHP, JS + pnpm, Docker, GitHub Actions + QA hooks, docs).
- All dependency updates must be done in a feature branch (`chore/update-*` naming) and submitted via PR.
- Before opening the PR, compare the branch with every open Dependabot PR and state which ones it supersedes.

**PHP Dependencies (composer.json / composer.lock):**
- Run `composer install` first (a stale `vendor/` falsifies the baseline), then `composer update --with-all-dependencies`.
- Keep all `symfony/*` constraints and `extra.symfony.require` on the same minor (`8.1.*`); move to a new minor only deliberately and after reading `UPGRADE-8.x.md`. Prefer the LTS minor (`x.4`) once it exists.
- After updating, `debug:container --deprecations` must be clean in `dev` and `test`; commit the regenerated `config/reference.php`.
- Flex recipe updates (`composer recipes:update`) go into a separate PR and are tried in a throwaway `git worktree`, never in the main checkout (see Working Rules).

**JavaScript Dependencies (package.json / pnpm-lock.yaml):**
- Run `CI=true pnpm update --latest` (updates the `package.json` ranges too; `CI=true` avoids TTY prompts).
- pnpm itself is pinned by `package.json` `packageManager` and run through corepack everywhere (CI, `Dockerfile.pinboard`, `make test`). It may be bumped, majors included, after reading the release notes; keep `pnpm-workspace.yaml` free of unknown keys (pnpm 12 fails on them).
- `pnpm-lock.yaml` must remain a single YAML document (`grep -c '^---$' pnpm-lock.yaml` → `0`); do not remove `pmOnFail: ignore` / `minimumReleaseAgeStrict: false` from `pnpm-workspace.yaml` without re-checking the Dependabot issues referenced there.
- `.nvmrc` follows the current Node.js **Active LTS** major; bump it together with the `node:<major>-alpine` images.

**Docker Images (Dockerfile.pinboard, docker/):**
- Always pin Docker base images to their full digest (SHA256 hash), never use untagged `latest` — this includes the dev stack (`docker/php-fpm/Dockerfile`, `docker/nginx/Dockerfile`, `docker/docker-compose*.yml`).
- When updating an image tag (e.g., `node:24-alpine`), fetch the current digest via `docker pull <image>` and update the hash.
- Example: `FROM node:24-alpine@sha256:ebfe2f90462722a7a4de65e91990e97fe0d401c70e0e762c5b53302f905ec1c1`
- Never remove an existing hash; always replace it with the new one if upgrading.
- Alpine packages use fuzzy pins (`pkg=~X.Y`), never exact `-rN` pins; check `apk policy` in the new base image and bump the pinned feature version when Alpine ships a new one.
- Build `Dockerfile.pinboard` and run the public-stack smoke test locally before pushing.

**GitHub Actions (.github/workflows/*.yml):**
- First-party GitHub actions (e.g., `actions/checkout`, `actions/setup-node`) use major version tags (`v7`, `v4`), which auto-track latest compatible patches.
- Third-party actions (e.g., `docker/build-push-action`, `shivammathur/setup-php`) must be pinned by full commit SHA with the release tag as a comment.
- To find the SHA: `gh api repos/<owner>/<repo>/git/ref/tags/<tag> --jq .object.sha`; if `.object.type` is `tag`, dereference it with `gh api repos/<owner>/<repo>/git/tags/<sha> --jq .object.sha`.
- Example: `uses: docker/build-push-action@c3c9e263c25d99ce0380d002d59b67737d91b0dc # v7.4.0`
- Never downgrade action major versions (e.g., v7 → v4); this is a backwards step and violates the forward-only rule.
- When Dependabot opens grouped PRs for action updates, validate they are actual upgrades before merging.

**QA tools (.pre-commit-config.yaml):**
- Bump hook `rev`s to the latest release and run the new versions over the whole repository — new releases add rules (hadolint 2.15 added DL3066).

**Commit Message Template for Dependency Updates:**
```
chore(deps): update <layer> dependencies to latest stable versions

- <package>: <old> → <new> (X updates total)
- <package>: <old> → <new>

All versions are stable releases. GitHub Actions already at latest majors.

Supersedes: #<PR> #<PR>
```
Use `fix(deps):` instead when the shipped image must be rebuilt (security fix, broken `apk` pin); see `docs/releasing.md` → "Version discipline". GitHub closing keywords do not close pull requests, so superseded Dependabot PRs are closed by Dependabot itself after merge or manually.

## Commit Messages

- Use Conventional Commits style.
- Keep commit titles short, imperative, and scoped when useful.
- Examples that fit this project:
  - `fix(ui): correct timer table layout`
  - `feat(auth): restore db-backed user sync`
  - `refactor(config): modernize sass build`
- Match the existing repository style: concise English messages with an optional scope.
