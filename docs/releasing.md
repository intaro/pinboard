# Releasing

Releases are fully automated via [Release Please](https://github.com/googleapis/release-please). No manual tagging or changelog editing is needed.

## How it works

1. A developer merges a PR into `master` (squash merge, Conventional Commit title).
2. The `Release Please` GitHub Actions workflow runs and inspects unreleased commits since the last tag.
3. Release Please opens (or updates) a **Release PR** titled `chore(main): release X.Y.Z`.
   - The PR contains a changelog diff for `CHANGELOG.md` and the new version number.
4. When a maintainer merges the Release PR, Release Please creates a GitHub Release and pushes a `vX.Y.Z` tag.
5. The `Docker` workflow triggers on the new release and publishes the image to Docker Hub.

## Version bump rules

Release Please derives the next version from Conventional Commits. Only **user-facing** commit
types trigger a release:

- `BREAKING CHANGE` footer or `!` suffix → **major** bump
- `feat` → **minor** bump
- `fix`, `perf`, `revert`, `deps` → **patch** bump

### Commits that do not trigger a release

These types are **not** release-triggering on their own and will not open a Release PR by
themselves:

- `docs`, `style`, `refactor`, `test`, `build`, `ci`, `chore`.

In particular, a documentation-only, CI-only, or tooling-only change does **not** produce a new
release (and therefore no new Docker image). Such commits are still valid — they are simply folded
into the changelog of the next release that *is* triggered by a release-triggering commit. They do
not start a release on their own.

> This is the default Release Please behaviour and matches the sibling
> [pinba_engine](https://github.com/XOlegator/pinba_engine) and
> [pinba_extension](https://github.com/XOlegator/pinba_extension) repositories. `release-please-config.json`
> intentionally keeps no custom `changelog-sections`, so `chore`/`refactor` do not inflate the version.

## Version discipline: what warrants a release

The version — the `vX.Y.Z` tag and the published `xolegator/pinboard:<version>` Docker image —
tracks the **shipped application** only. Pick a commit type by *what the change actually affects*,
not by how big it feels:

- **Application code and the runtime image** — anything that changes the deployed Pinboard app or
  the image users pull: `src/**`, `templates/**`, `assets/**`, `migrations/**`, `config/**`,
  `public/**`, `bin/**`, runtime dependencies (`composer.json` / `composer.lock`, `package.json` /
  `pnpm-lock.yaml`), and the shipped image definition (`Dockerfile.pinboard` and the runtime pieces
  under `docker/` that change the published image — e.g. an nginx/php-fpm apk pin bump). Use
  `feat:` (a new capability → `minor`) or `fix:` (a bug or behaviour correction → `patch`, this
  includes security-relevant dependency fixes). These cut a new version, tag, and Docker image.
- **Everything else** — CI and automation (`.github/**`: `ci.yml`, `docker.yml`,
  `release-please.yml`, `dependabot.yml`), documentation (`docs/**`, `README.md`, `*.md`,
  `CHANGELOG*`), tests and QA config (`tests/**`, `phpunit.xml.dist`, `phpstan.neon`,
  `.php-cs-fixer.dist.php`, `.pre-commit-config.yaml`) and dev-only tooling (`Makefile`, the dev
  stack under `docker/`). Use `ci:`, `build:`, `chore:`, `docs:`, `test:`, `style:` or `refactor:`.
  These do **not** bump the version: the shipped image is identical, so there is no reason to
  publish a new release or rebuild an identical Docker image.

Why it matters: every release fires the `docker.yml` workflow and pushes a new
`xolegator/pinboard:<version>` + `:latest`. A new tag for a docs or CI tweak forces an identical
image rebuild and churns the changelog for no user benefit. Packaging or CI churn must never inflate
the application's version.

Two rules of thumb:

- **Prefer `fix` over `feat` for corrections.** `feat` is for new capability only; making
  previously surprising or incorrect behaviour correct is a `fix` (a `patch`).
- **Routine dependency bumps ride the next real release.** Dependabot's default `chore(deps): ...`
  does not cut a release; re-type it as `fix(deps): ...` only when the update is a fix that must
  ship on its own (e.g. a security advisory, as in 2.1.6).

Examples:

- Bump an nginx apk pin in `Dockerfile.pinboard` → `fix(docker): ...` (`patch`, new image — as in 2.1.7).
- Make the error-code threshold configurable → `feat(aggregate): ...` (`minor`).
- Add status badges or reword the README → `docs(readme): ...` (no release).
- Bump a GitHub Action version → `ci: ...` (no release).

## Docker image publishing

The `docker.yml` workflow fires on every published GitHub Release. It:

- Builds `Dockerfile.pinboard` from the release commit.
- Tags the image as `xolegator/pinboard:<version>` and `xolegator/pinboard:latest`.
- Pushes both tags to Docker Hub using BuildKit layer caching.

### Required repository secrets

| Secret              | Purpose                                      |
|---------------------|----------------------------------------------|
| `DOCKERHUB_USERNAME`| Docker Hub account username                  |
| `DOCKERHUB_TOKEN`   | Docker Hub access token (read/write, no delete) |

Add these under **Settings → Secrets and variables → Actions** in the GitHub repository.

## Manifest seed

`.release-please-manifest.json` records the current released version (`2.0.0`). Release Please reads this to know where to start. Do not edit this file manually — Release Please updates it automatically when it creates a Release PR.

## Configuration

`release-please-config.json` controls:

- `release-type: simple` — manages `CHANGELOG.md` and git tags; does not modify `composer.json`.
- `changelog-path: CHANGELOG.md` — separate from the hand-maintained `CHANGELOG` file which covers the pre-2.x history.
- No custom `changelog-sections` — the config relies on Release Please defaults so that only
  user-facing types (`feat`, `fix`, `perf`, `revert`, `deps`, breaking) trigger a release and appear
  in the changelog, while `docs`/`ci`/`chore`/`refactor`/`test`/`build`/`style` are hidden and
  non-triggering. See "Version bump rules" above.

## Branch protection requirements

For the workflow to function correctly, `master` should have the following branch protection rules active:

- Require a pull request before merging (no direct pushes).
- Require status checks to pass before merging: `PHP CS Fixer`, `PHPStan`, `PHPUnit (PHP 8.4)`, `PHPUnit (PHP 8.5)`.
- Require branches to be up to date before merging.
- Allow repository admins to bypass (so Release Please can push the release commit).

See [GitHub documentation](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches) for how to configure rulesets.
