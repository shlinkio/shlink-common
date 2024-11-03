# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [6.5.0] - 2024-11-03
### Added
* *Nothing*

### Changed
* Extract configuration-related code from `EntityManagerFactory` into `ConfigurationFactory`.
* Update to shlinkio coding standard 2.4.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* Fix calls to `SCAN` commands to redis >=7.4 when cursor is `null`.


## [6.4.0] - 2024-10-27
### Added
* Add support for `endroid/qr-code` 6.0

### Changed
* Switch to xdebug for code coverage reports, as pcov is not marking functions as covered
* Remove references to `ValinorConfigFactory` from `shlinkio/shlink-config`.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [6.3.0] - 204-10-04
### Added
* Add support to define redis database index in redis connection URIs.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [6.2.0] - 2024-08-11
### Added
* *Nothing*

### Changed
* Update dependencies

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [6.1.0] - 2024-04-14
### Added
* Allow current request ID to be set and read in `RequestIdMiddleware`.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [6.0.0] - 2024-03-03
### Added
* Add new `RequestIdMiddleware`.
* Add support for Doctrine ORM 3.0.0
* Add support for `endroid/qr-code` 5.0

### Changed
* Update dependencies
* Update to PHPUnit 11
* Inputs created with `InputFactory` are now not required by default. Also, the `required` param is now always the last one.

### Deprecated
* *Nothing*

### Removed
* Remove `BackwardsCompatibleMonologProcessorDelegator`.
* Remove support for redis URIs with just a password in place of the username (like `tcp://password@1.2.3.4`).
* Remove support for non-URL-encoded credentials in redis URIs.
* Remove `json_decode` and `json_encode` functions.
* Remove support for openswoole.
* Remove support for Doctrine ORM 2.x
* Remove dependency on injection and mutation tests.
* Remove `InputFactoryTrait` in favor of `InputFactory` class with static factory methods.

### Fixed
* *Nothing*


## [5.7.1] - 2023-12-17
### Added
* *Nothing*

### Changed
* Remove dependency on functional-php

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [5.7.0] - 2023-11-25
### Added
* [#134](https://github.com/shlinkio/shlink-common/issues/134) Support redis server URIs with URL-encoded credentials.
* Support encrypted redis server connections.
* Support encrypted RabbitMQ server connections.
* Add support for Chronos 3.0
* Add support for PHP 8.3
* Allow disabling access logs for requests to some paths

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* Drop support for PHP 8.1

### Fixed
* *Nothing*


## [5.6.0] - 2023-09-22
### Added
* Add a `NamespaceStore` class that can be used to wrap `symfony/lock` stores and prefix key resources.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [5.5.1] - 2023-05-28
### Added
* *Nothing*

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* [#125](https://github.com/shlinkio/shlink-common/issues/125) Make sure `AccessLogMiddleware` logs request's query string.


## [5.5.0] - 2023-05-23
### Added
* Add `shlinkio/shlink-json` dependency.
* Add `AccessLogMiddleware` and improve `LoggerFactory` options.

### Changed
* Update to PHPUnit 10.1
* Update to Infection 0.27
* Make sure RabbitMQ connections are created lazy.
* Use Guzzle's `CurlHanddler` instead of `CurlMultiHandler` to avoid 100% CPU usage due to infinite `while` loop.

### Deprecated
* Deprecate `json_encode` and `json_decode` functions.

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [5.4.0] - 2023-03-30
### Added
* [#120](https://github.com/shlinkio/shlink-common/issues/120) Add support for `lcobucci/jwt` 5.0.

### Changed
* Updated to PHPUnit 10

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [5.3.1] - 2023-02-04
### Added
* *Nothing*

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* Fixed incompatibility between predis 2 and symfony/cache making it impossible to clear cache


## [5.3.0] - 2023-01-28
### Added
* *Nothing*

### Changed
* Changed `EntityManagerFactory` so that it creates a configuration with symfony ghost objects for proxies.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [5.2.0] - 2022-12-16
### Added
* Added support for credentials on redis servers, either just password or both username and password.
* Added `EntityRepositoryFactory` helper factory for entity repositories.

### Changed
* Migrated infection config to json5
* Migrated from prophecy to PHPUnit mocks
* Extended boolean input to make sure it casts "false" string as false.

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* Ensured redis servers are trimmed inside RedisFactory.


## [5.1.0] - 2022-09-18
### Added
* Added support for PSR-16, simple cache.

### Changed
* *Nothing*

### Deprecated
* *Nothing*

### Removed
* *Nothing*

### Fixed
* *Nothing*


## [5.0.0] - 2022-08-08
### Added
* *Nothing*

### Changed
* Updated dependencies

### Deprecated
* *Nothing*

### Removed
* Removed deprecated stuff.

### Fixed
* *Nothing*


## [4.5.0] - 2022-08-05
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
