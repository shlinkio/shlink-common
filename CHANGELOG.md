# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [Unreleased]
### Added
* [#60](https://github.com/shlinkio/shlink-common/issues/60) Added support for `pagerfanta/core` as a pagination system.
* [#64](https://github.com/shlinkio/shlink-common/issues/64) Added new input factory methods.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* [#63](https://github.com/shlinkio/shlink-common/issues/63) Removed support for `laminas/laminas-paginator`. Use `pagerfanta/core` instead.

### Fixed
* *Nothing*


## [3.4.0] - 2021-01-17
### Added
* Added support for `lcobucci/jwt:4.0` stable version.
* Updated to `akrabat/ip-address-middleware` 2.0
* [#58](https://github.com/shlinkio/shlink-common/issues/58) Added support to define custom base repositories for the entity manager.

### Changed
* Migrated build to Github Actions.

### Deprecated
* *Nothing*

### Removed
* Dropped support for `cakephp/chronos` 1.0

### Fixed
* *Nothing*


## [3.3.2] - 2020-11-22
### Added
* *Nothing*

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* Fixed errors when using mercure 0.10.


## [3.3.1] - 2020-11-22
### Added
* *Nothing*

### Changed
* Changed all phpunit assertions to use static access.

### Deprecated
* *Nothing*

### Removed
* Removed dependency on `league/plates`.

### Fixed
* Fixed compatibility with `lcobucci/jwt:4.0@beta`.


## [3.3.0] - 2020-11-06
### Added
* Explicitly added compatibility with PHP 8
* Added support for Chronos 2.0

### Changed
* Updated to `infection` v0.20

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [3.2.1] - 2020-10-25
### Added
* *Nothing*

### Changed
* Added PHP 8 to the build matrix, allowing failures on it.
* Updated to composer 2.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [3.2.0] - 2020-06-28
### Added
* Added support for Guzzle 7.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [3.1.0] - 2020-05-09
### Added
* [#42](https://github.com/shlinkio/shlink-common/issues/42) Added utilities to work with a mercure hub.

### Changed
* [#44](https://github.com/shlinkio/shlink-common/issues/44) Updated `phpunit` to v9, `infection` to v0.16 and `phpstan` to v0.12.
* [#46](https://github.com/shlinkio/shlink-common/issues/46) Created `ReopeningEntityManagerInterface`, which is implemented by the `ReopeningEntityManager`.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [3.0.0] - 2020-03-14
### Added
* *Nothing*

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* [#29](https://github.com/shlinkio/shlink-common/issues/29) Removed everything which is now part of `shlinkio/shlink-config`.

### Fixed
* *Nothing*


## [2.8.0] - 2020-03-06
### Added
* [#37](https://github.com/shlinkio/shlink-common/issues/37) Middlewares can now be registered on the http client.
* [#40](https://github.com/shlinkio/shlink-common/issues/40) Replaced coccur/sluggify by symfony/string.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* [#39](https://github.com/shlinkio/shlink-common/issues/39) Fixed `SluggerFilter` so that it does not return null when an empty string is provided.


## [2.7.0] - 2020-01-29
### Added
* [#35](https://github.com/shlinkio/shlink-common/issues/35) Allowed entity mapping config files to be loaded on a functional way and to get the EM config to be passed.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.6.0] - 2020-01-28
### Added
* [#32](https://github.com/shlinkio/shlink-common/issues/32) Created new `ExcludingValidatorChain` which allows a list of validators to be opassed and considers the value valid as soon as one of them passes.
* [#32](https://github.com/shlinkio/shlink-common/issues/32) Added `createDateInput` and `createArrayInput` to the `InputFactoryTrait`. The first one makes use of the `ExcludingValidatorChain` to define two valid date formats, `ATOM` and `Y-m-d`.
* [#31](https://github.com/shlinkio/shlink-common/issues/31) Enhanced `ErrorLogger` so that it logs "controlled" errors as debug, the same way it used to do before adding the problem-details package.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.5.0] - 2020-01-03
### Added
* *Nothing*

### Changed
* [#25](https://github.com/shlinkio/shlink-common/issues/25) Updated to [shlinkio/php-coding-standard](https://github.com/shlinkio/php-coding-standard) v2.1.
* [#26](https://github.com/shlinkio/shlink-common/issues/26) Migrated from Zend Framework components to [Laminas](https://getlaminas.org/).

### Deprecated
* *Nothing*

### Removed
* [#24](https://github.com/shlinkio/shlink-common/issues/24) Dropped support for PHP 7.2 and 7.3

### Fixed
* *Nothing*


## [2.4.0] - 2019-11-30
### Added
* *Nothing*

### Changed
* Bumped required symfony version to v5.0

### Deprecated
* *Nothing*

### Removed
* [#21](https://github.com/shlinkio/shlink-common/issues/21) Removed direct dependency on monolog.

### Fixed
* *Nothing*


## [2.3.0] - 2019-11-22
### Added
* Updated to latest symfony and doctrine dependency versions
* Created `ErrorLogger` that can be used to listen for `ErrorHandler` and `ProblemDetailMiddleware` errors in order to log them on a PSR logger.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.2.1] - 2019-11-01
### Added
* *Nothing*

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* Made `ReopeningEntityManager` to have to be actively opened by the `CloseDbConnectionMiddleware`, so that it behaves closer to fast-cgi contexts.


## [2.2.0] - 2019-10-27
### Added
* *Nothing*

### Changed
* [#17](https://github.com/shlinkio/shlink-common/issues/17) Simplified `ReopeningEntityManager` so that it expects a simpler EM creation function.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.1.0] - 2019-10-11
### Added
* [#14](https://github.com/shlinkio/shlink-common/issues/14) Created a `HostAndPortValidator` which is capable of validating values such as `example.com` or `example.com:8080`.

### Changed
* [#5](https://github.com/shlinkio/shlink-common/issues/5) Updated to latest [endroid/qr-code](https://github.com/endroid/qr-code) version.
* Updated to [shlinkio/php-coding-standard](https://github.com/shlinkio/php-coding-standard) v2.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [2.0.0] - 2019-09-11
### Added
* [#4](https://github.com/shlinkio/shlink-common/issues/4) Finished documentation.

### Changed
* [#2](https://github.com/shlinkio/shlink-common/issues/2) Increased code coverage to 98%.
* [#1](https://github.com/shlinkio/shlink-common/issues/1) Increased mutation score to 84%.

### Deprecated
* *Nothing*

### Removed
* [#7](https://github.com/shlinkio/shlink-common/issues/7) Removed anything related with i18n.

    * `TranslatorFactory`.
    * `TranslatorExtension`.
    * `LocaleMiddleware`.

### Fixed
* *Nothing*


## [1.0.0] - 2019-08-12
### Added
* First stable release

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*
