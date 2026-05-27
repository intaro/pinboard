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

Common types and their effect on versioning:

| Type       | SemVer bump | Appears in changelog |
|------------|-------------|----------------------|
| `feat`     | minor       | yes                  |
| `fix`      | patch       | yes                  |
| `perf`     | patch       | yes                  |
| `refactor` | patch       | yes                  |
| `chore`    | patch       | yes                  |
| `docs`     | patch       | hidden               |
| `test`     | patch       | hidden               |
| `ci`       | patch       | hidden               |

A `!` suffix or `BREAKING CHANGE:` footer triggers a **major** bump regardless of type.

Useful scopes for this project: `ui`, `auth`, `api`, `db`, `config`, `docker`, `ci`, `docs`.

## Creating a pull request

1. Create a branch from the latest `master`.
2. Make commits with Conventional Commit titles.
3. Push and open a PR against `master`.
4. CI runs three jobs automatically: `PHP CS Fixer`, `PHPStan`, `PHPUnit`.
5. All three must pass before the PR can be merged.
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
