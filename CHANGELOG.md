# Changelog

## [2.1.4](https://github.com/intaro/pinboard/compare/v2.1.3...v2.1.4) (2026-05-30)


### Bug Fixes

* **ui:** harden live page rendering ([#163](https://github.com/intaro/pinboard/issues/163)) ([a7a71b1](https://github.com/intaro/pinboard/commit/a7a71b17a04cc0e9c3653cd5b570d8abd26630ce))

## [2.1.3](https://github.com/intaro/pinboard/compare/v2.1.2...v2.1.3) (2026-05-30)


### Bug Fixes

* **ci:** trigger docker publish from release automation ([#160](https://github.com/intaro/pinboard/issues/160)) ([4aa3e92](https://github.com/intaro/pinboard/commit/4aa3e920ea06a6c774da39c14485649eb2ebc94f))
* **db:** retain tag info with cleanup ([#162](https://github.com/intaro/pinboard/issues/162)) ([274d671](https://github.com/intaro/pinboard/commit/274d67112c21c936e2d67925e32eba2f8fba5b21))

## [2.1.2](https://github.com/intaro/pinboard/compare/v2.1.1...v2.1.2) (2026-05-30)


### Bug Fixes

* **ui:** align request time pagination count ([#158](https://github.com/intaro/pinboard/issues/158)) ([54e1fc6](https://github.com/intaro/pinboard/commit/54e1fc6e30b13de25d233d466a9ccb6cf64940ec))

## [2.1.1](https://github.com/intaro/pinboard/compare/v2.1.0...v2.1.1) (2026-05-27)


### Bug Fixes

* **ci:** set explicit component to match release tag format ([#156](https://github.com/intaro/pinboard/issues/156)) ([4bec55f](https://github.com/intaro/pinboard/commit/4bec55f5de1aa7547ab3b031086fd7677c890fb4))

## [2.1.0](https://github.com/intaro/pinboard/compare/v2.0.0...v2.1.0) (2026-05-27)


### Features

* **aggregate:** make error HTTP status threshold configurable via APP_MIN_ERROR_CODE ([#151](https://github.com/intaro/pinboard/issues/151)) ([9b2073c](https://github.com/intaro/pinboard/commit/9b2073c))


### Bug Fixes

* **ci:** add workflow_dispatch to bootstrap first Release PR ([#154](https://github.com/intaro/pinboard/issues/154)) ([8bd1a5c](https://github.com/intaro/pinboard/commit/8bd1a5c))
* **ci:** set last-release-sha so release-please finds its anchor ([#152](https://github.com/intaro/pinboard/issues/152)) ([0a2e1b1](https://github.com/intaro/pinboard/commit/0a2e1b1))
* **ci:** upgrade actions/checkout and actions/cache to v5 (Node.js 24) ([#149](https://github.com/intaro/pinboard/issues/149)) ([2e0256d](https://github.com/intaro/pinboard/commit/2e0256d))
* **ci:** upgrade release-please-action to v5 (Node.js 24) ([#150](https://github.com/intaro/pinboard/issues/150)) ([739d541](https://github.com/intaro/pinboard/commit/739d541))
* **ci:** use empty-string manifest key to match release-please root component ([#153](https://github.com/intaro/pinboard/issues/153)) ([8be6695](https://github.com/intaro/pinboard/commit/8be6695))


### Maintenance

* **ci:** add GitHub Actions, release-please, and Docker publish automation ([#148](https://github.com/intaro/pinboard/issues/148)) ([4928c2a](https://github.com/intaro/pinboard/commit/4928c2a))
