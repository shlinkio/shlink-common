# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [Unreleased]
### Added
* Added new `MonologFactory` for monolog 3, and a couple new logger utilities.
* Added new `RabbitMqPublishingHelper`, to simplify publishing in RabbitMQ queues.
* Added new `RedisPublishingHelper`, to simplify publishing in redis pub/sub queues.
* Added new `MercureHubPublishingHelper`, to simplify publishing in a mercure hub topic.

### Changed
* Upgraded to predis 2.0.0

### Deprecated
* Deprecated named constructors in `DateRange`. Provided replacements with better semantics.

### Removed
* Dropped support for PHP 8.0

### Fixed
* *Nothing*


## [4.4.0] - 2022-01-23
### Added
* Created logic to build filtering and validation fields for "order by".

### Changed
* Updated to infection 0.26, enabling HTML reports.
* Added explicitly enabled composer plugins to composer.json.

### Deprecated
* *Nothing*

### Removed
* [#98](https://github.com/shlinkio/shlink-common/issues/98) Removed integration with doctrine/cache by manually creating the doctrine config object.

### Fixed
* *Nothing*


## [4.3.0] - 2022-01-07
### Added
* Added support in paginator utils to provide a custom data prop name.
* Added support to define a default lifetime in cache adapters.

### Changed
* *Nothing*

### Deprecated
* Deprecated `Shlinkio\Shlink\Common\env` function. Use `Shlinkio\Shlink\Config\env` as a direct replacement.

### Removed
* Dropped support for Symfony 5.

### Fixed
* *Nothing*


## [4.2.1] - 2021-12-21
### Added
* *Nothing*

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* Ensured `CloseDbConnectionMiddleware` closes the EntityManager instead of clearing it.


## [4.2.0] - 2021-12-12
### Added
* Added support for openswoole.
* Updated to pagerfanta 3.5.
* Added `json_encode` function with implicit conversion of errors to exceptions.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* Added missing code style checks to functions folder.


## [4.1.0] - 2021-12-02
### Added
* Added support for symfony/mercure 0.6
* Added official support for PHP 8.1
* Added support for Symfony 6

### Changed
* Moved ci workflow to external repo and reused
* Updated to phpstan 1.0

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [4.0.0] - 2021-10-08
### Added
* [#80](https://github.com/shlinkio/shlink-common/issues/80) Added support for redis cache with redis sentinel

### Changed
* [#77](https://github.com/shlinkio/shlink-common/issues/77) Replaced doctrine/cache adapters by symfony/cache, complying with PSR-6 and PSR-16 in the process.

    The project still depends on doctrine/cache 2.0, and wraps the PSR-6 adapter into a doctrine cache that can be used with other doctrine packages.

* Updated to infection 0.24
* [#79](https://github.com/shlinkio/shlink-common/issues/79) Added experimental builds under PHP 8.1
* [#76](https://github.com/shlinkio/shlink-common/issues/76) Increased required phpstan level to 8

### Deprecated
* *Nothing*

### Removed
* [#75](https://github.com/shlinkio/shlink-common/issues/75) Dropped support for PHP 7.4.
* [#74](https://github.com/shlinkio/shlink-common/issues/74) Removed everything that was deprecated from v3.*

### Fixed
* *Nothing*


## [3.7.0] - 2021-05-21
### Added
* [#70](https://github.com/shlinkio/shlink-common/issues/70) Added support for `symfony/mercure` 0.5.
* [#72](https://github.com/shlinkio/shlink-common/issues/72) Added ability to register event listeners in the EntityManager through the `EntityManagerFactory`.

### Changed
* Updated to infection 0.23

### Deprecated
* *Nothing*

### Removed
* [#70](https://github.com/shlinkio/shlink-common/issues/70) Dropped support for `symfony/mercure` 0.4.

### Fixed
* *Nothing*


## [3.6.0] - 2021-02-28
### Added
* [#68](https://github.com/shlinkio/shlink-common/issues/68) Added support for `endroid/qr-code` 4.0.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* [#68](https://github.com/shlinkio/shlink-common/issues/68) Dropped support for `endroid/qr-code` 3.*.

### Fixed
* *Nothing*


## [3.5.0] - 2021-02-12
### Added
* [#60](https://github.com/shlinkio/shlink-common/issues/60) Added support for `pagerfanta/core` as a pagination system.
* [#64](https://github.com/shlinkio/shlink-common/issues/64) Added new input factory methods.
* Added named constructors to `DateRange`.
* Created new `ContentLengthMiddleware`.

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
