# Contributing

## Workflow overview

All changes enter `master` exclusively via pull request. Direct pushes to `master` are blocked by branch protection rules.

```
feature-branch  →  PR  →  CI (cs-fixer, phpstan, phpunit)  →  merge to master
```

After merge, CI runs again on `master`. Release Please then opens or updates a "Release PR" that tracks the next version. When that Release PR is merged, a GitHub Release is created automatically and the Docker image is published.

## Branch naming

Use a descriptive lowercase name, optionally prefixed by the change type:

```
fix/timer-table-layout
feat/db-user-sync
refactor/sass-build
chore/composer-update
```

No enforced pattern beyond keeping it readable.

## Commit messages

This project follows [Conventional Commits](https://www.conventionalcommits.org/). Every commit reachable from `master` must have a conforming title because Release Please reads them to determine the next version and generate the changelog.

```
type(scope): short imperative description
```

Common types and their effect on versioning. Only user-facing types trigger a release; the rest are
folded into the next release's changelog but do **not** cut a version or Docker image on their own:

| Type       | SemVer bump      | Triggers release? | Appears in changelog |
|------------|------------------|-------------------|----------------------|
| `feat`     | minor            | yes               | yes                  |
| `fix`      | patch            | yes               | yes                  |
| `perf`     | patch            | yes               | yes                  |
| `deps`     | patch            | yes               | yes                  |
| `revert`   | patch            | yes               | yes                  |
| `refactor` | none             | **no**            | hidden               |
| `chore`    | none             | **no**            | hidden               |
| `docs`     | none             | **no**            | hidden               |
| `test`     | none             | **no**            | hidden               |
| `build`    | none             | **no**            | hidden               |
| `style`    | none             | **no**            | hidden               |
| `ci`       | none             | **no**            | hidden               |

A `!` suffix or `BREAKING CHANGE:` footer triggers a **major** bump regardless of type.

Pick the type by *what the change affects*, not how big it feels — see
[docs/releasing.md → "Version discipline"](releasing.md) for the full rule. In short: application
code and the shipped Docker image use `feat`/`fix`; CI, docs, tests and tooling use
`ci`/`docs`/`test`/`chore` and do not cut a release.

Useful scopes for this project: `ui`, `auth`, `api`, `db`, `config`, `docker`, `ci`, `docs`.

## Creating a pull request

1. Create a branch from the latest `master`.
2. Make commits with Conventional Commit titles.
3. Push and open a PR against `master`.
4. CI runs automatically: `PHP CS Fixer`, `PHPStan`, `PHPUnit` (PHP 8.4 and 8.5), `Coverage`,
   `Frontend build` (pnpm frozen install + production build) and
   `Docker image + stack smoke test` (builds `Dockerfile.pinboard` and boots the public
   compose stack against it).
5. All of them must pass before the PR can be merged.
6. Request review or merge it yourself if you are a maintainer.

## Merge policy

Use **squash merge** when merging PRs. The squash commit title becomes the changelog entry, so make sure it is a valid Conventional Commit title (GitHub pre-fills it from the PR title).

After merge, CI reruns on `master`. If it passes, Release Please updates or creates its Release PR.

## Code style

Run the following before pushing to avoid CI failures:

```bash
vendor/bin/php-cs-fixer fix
vendor/bin/phpstan analyse --no-progress
vendor/bin/phpunit --no-progress
```

See [testing.md](testing.md) for the full checklist and [standards.md](standards.md) for code conventions.
