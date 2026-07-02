# Security Policy

## Supported Branch

Security fixes are developed on the active `master` branch and shipped as tagged
releases (`vX.Y.Z`) via the Release Please pipeline.

## Supported Runtime Matrix

Pinboard tracks currently supported PHP branches, matching `composer.json`
(`php: >=8.4`) and the CI test matrix. As of 2026-07-02, the active matrix is:

- `PHP 8.4`
- `PHP 8.5`

Older end-of-life PHP branches are not supported for security maintenance.

Pinboard reads data from a Pinba storage engine
([XOlegator/pinba_engine](https://github.com/XOlegator/pinba_engine)); vulnerabilities
in the engine or in the [Pinba PHP extension](https://github.com/XOlegator/pinba_extension)
should be reported to those projects.

## Reporting A Vulnerability

Please do not open a public issue for a suspected security vulnerability.

Report it privately via GitHub's
[private vulnerability reporting](https://github.com/intaro/pinboard/security/advisories/new),
or by email to:

- Oleg Ekhlakov <o.ekhlakov@protonmail.com>

Include, when possible:

- affected Pinboard version or commit;
- affected PHP version;
- reproduction steps;
- whether the issue impacts confidentiality, integrity, availability, or data correctness;
- whether the issue is authenticated-only or reachable pre-authentication.

## Response Policy

- Acknowledgement target: reasonable best effort.
- Fixes should preserve the existing public contract unless a breaking security
  mitigation is unavoidable.
- Public release notes are published after a fix is prepared or released.
