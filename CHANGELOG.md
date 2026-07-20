# Changelog

## [2.1.17](https://github.com/intaro/pinboard/compare/v2.1.16...v2.1.17) (2026-07-20)


### Bug Fixes

* harden pinba aggregation and local test stack ([f0b047a](https://github.com/intaro/pinboard/commit/f0b047a3cda897d6e8de875d6d3b85dce3595c68))

## [2.1.16](https://github.com/intaro/pinboard/compare/v2.1.15...v2.1.16) (2026-07-18)


### Bug Fixes

* enforce per-user hosts access across legacy configs, timers and user migration ([#207](https://github.com/intaro/pinboard/issues/207)) ([3b672e2](https://github.com/intaro/pinboard/commit/3b672e2b0da455546dd7c24d24157aeffd0503d7))

## [2.1.15](https://github.com/intaro/pinboard/compare/v2.1.14...v2.1.15) (2026-07-17)


### Bug Fixes

* **aggregate:** reject rounded float sentinels ([#205](https://github.com/intaro/pinboard/issues/205)) ([8cfc8d0](https://github.com/intaro/pinboard/commit/8cfc8d05b187b4e361b0adbb99d328f4e08d2fcc))

## [2.1.14](https://github.com/intaro/pinboard/compare/v2.1.13...v2.1.14) (2026-07-17)


### Bug Fixes

* **docker:** refresh php alpine pins ([#203](https://github.com/intaro/pinboard/issues/203)) ([87d5ddd](https://github.com/intaro/pinboard/commit/87d5ddd9cdc05e120a5e4ae94fcd972367f02463))

## [2.1.13](https://github.com/intaro/pinboard/compare/v2.1.12...v2.1.13) (2026-07-17)


### Bug Fixes

* **aggregate:** drop pinba float sentinel percentiles ([#201](https://github.com/intaro/pinboard/issues/201)) ([c011c1b](https://github.com/intaro/pinboard/commit/c011c1b0385352d9f42bb52c6a7f35f0ee03e9e7))

## [2.1.12](https://github.com/intaro/pinboard/compare/v2.1.11...v2.1.12) (2026-07-17)


### Bug Fixes

* **aggregate:** harden percentile value guards ([#199](https://github.com/intaro/pinboard/issues/199)) ([dcc0b6f](https://github.com/intaro/pinboard/commit/dcc0b6fef0bfa163ae1dc604c014f6c11dad1ead))

## [2.1.11](https://github.com/intaro/pinboard/compare/v2.1.10...v2.1.11) (2026-07-17)


### Bug Fixes

* **aggregate:** guard percentile inserts in strict mysql ([#195](https://github.com/intaro/pinboard/issues/195)) ([dfb8464](https://github.com/intaro/pinboard/commit/dfb84645f37427480ef30566e6e009569ef888a4))

## [2.1.10](https://github.com/intaro/pinboard/compare/v2.1.9...v2.1.10) (2026-07-17)


### Bug Fixes

* **docker:** run public image rootless by default ([#193](https://github.com/intaro/pinboard/issues/193)) ([6730bd0](https://github.com/intaro/pinboard/commit/6730bd06b7c54872c66dba1cf3f3100097f9f09a))

## [2.1.9](https://github.com/intaro/pinboard/compare/v2.1.8...v2.1.9) (2026-07-16)


### Bug Fixes

* **docker:** move pinboard runtime and widen host fields ([#191](https://github.com/intaro/pinboard/issues/191)) ([1c60af2](https://github.com/intaro/pinboard/commit/1c60af25a9824fdb84199d1362bf9a52e08bdfb2))

## [2.1.8](https://github.com/intaro/pinboard/compare/v2.1.7...v2.1.8) (2026-07-12)


### Bug Fixes

* **docker:** sessions-only volume, rolling engine tags, repair image build ([#187](https://github.com/intaro/pinboard/issues/187)) ([80cf471](https://github.com/intaro/pinboard/commit/80cf4718065127600f78bdedc8bd766ca2763630))

## [2.1.7](https://github.com/intaro/pinboard/compare/v2.1.6...v2.1.7) (2026-07-02)


### Bug Fixes

* **docker:** bump nginx apk pin to 1.28.3-r4 ([#171](https://github.com/intaro/pinboard/issues/171)) ([8237d2b](https://github.com/intaro/pinboard/commit/8237d2b900880f0def7ffcd99e53a4ee2fe538a6))

## [2.1.6](https://github.com/intaro/pinboard/compare/v2.1.5...v2.1.6) (2026-07-02)


### Bug Fixes

* **deps:** resolve 30 Dependabot security advisories ([#169](https://github.com/intaro/pinboard/issues/169)) ([97c6c86](https://github.com/intaro/pinboard/commit/97c6c8635241232c084e062479ab354be2e824e9))

## [2.1.5](https://github.com/intaro/pinboard/compare/v2.1.4...v2.1.5) (2026-06-07)


### Bug Fixes

* **aggregate:** release lock on failure ([#166](https://github.com/intaro/pinboard/issues/166)) ([0fbb82d](https://github.com/intaro/pinboard/commit/0fbb82deb6e15694f6d5bf98d181270cfb94c0c0))

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
