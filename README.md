# Shlink Common

[![Build Status](https://img.shields.io/github/workflow/status/shlinkio/shlink-common/Continuous%20integration/main?logo=github&style=flat-square)](https://github.com/shlinkio/shlink-common/actions?query=workflow%3A%22Continuous+integration%22)
[![Code Coverage](https://img.shields.io/codecov/c/gh/shlinkio/shlink-common/main?style=flat-square)](https://app.codecov.io/gh/shlinkio/shlink-common)
[![Latest Stable Version](https://img.shields.io/github/release/shlinkio/shlink-common.svg?style=flat-square)](https://packagist.org/packages/shlinkio/shlink-common)
[![License](https://img.shields.io/github/license/shlinkio/shlink-common.svg?style=flat-square)](https://github.com/shlinkio/shlink-common/blob/main/LICENSE)
[![Paypal donate](https://img.shields.io/badge/Donate-paypal-blue.svg?style=flat-square&logo=paypal&colorA=aaaaaa)](https://slnk.to/donate)

This library provides some utils and conventions for web apps. It's main purpose is to be used on [Shlink](https://github.com/shlinkio/shlink) project, but any PHP project can take advantage.

Most of the elements it provides require a [PSR-11](https://www.php-fig.org/psr/psr-11/) container, and it's easy to integrate on [mezzio](https://github.com/mezzio/mezzio) applications thanks to the `ConfigProvider` it includes.

## Install

Install this library using composer:

    composer require shlinkio/shlink-common

> This library is also a mezzio module which provides its own `ConfigProvider`. Add it to your configuration to get everything automatically set up.

## Cache

A [doctrine cache](https://www.doctrine-project.org/projects/doctrine-cache/en/1.8/index.html) adapter is registered, which returns different instances depending on your configuration:
 
 * An `ArrayCache` instance when the `debug` config is set to true or when the APUc extension is not installed and the `cache.redis` config is not defined.
 * An `ApcuCache`instance when no `cache.redis` is defined and the APCu extension is installed.
 * A `PredisCache` instance when the `cache.redis` config is defined.
 
 Any of the adapters will use the namespace defined in `cache.namespace` config entry.
 
 ```php
<?php

declare(strict_types=1);

return [

    'debug' => false,

    'cache' => [
        'namespace' => 'my_namespace',
        'redis' => [
            'servers' => [
                'tcp://1.1.1.1:6379',
                'tcp://2.2.2.2:6379',
                'tcp://3.3.3.3:6379',
            ],
        ],
    ],

];
```

When the `cache.redis` config is provided, a set of servers is expected. If only one server is provided, this library will treat it as a regular server, but if several servers are defined, it will treat them as a redis cluster and expect the servers to be configured as such.

## Middlewares

This module provides a set of useful middlewares, all registered as services in the container:

* `CloseDbConnectionMiddleware`:

    Should be an early middleware in the pipeline. It makes use of the EntityManager that ensure the database connection is closed at the end of the request.

    It should be used when serving an app with a non-blocking IO server (like Swoole or ReactPHP), which persist services between requests.

* `IpAddress` (from [akrabat/ip-address-middleware](https://github.com/akrabat/ip-address-middleware) package):

    Improves detection of the remote IP address.

    The set of headers which are inspected in order to search for the address can be customized using this configuration:

    ```php
    <?php

    declare(strict_types=1);

    return [

        'ip_address_resolution' => [
            'headers_to_inspect' => [
                'CF-Connecting-IP',
                'True-Client-IP',
                'X-Real-IP',
                'Forwarded',
                'X-Forwarded-For',
                'X-Forwarded',
                'X-Cluster-Client-Ip',
                'Client-Ip',
            ],
        ],

    ];
    ```

## Doctrine integration

Some doctrine-related services are provided, that can be customized via configuration:

### EntityManager

The EntityManager service can be fetched using names `em` or `Doctrine\ORM\EntityManager`.

In any case, it will come decorated so that it is reopened automatically after having been closed.

The EntityManager can be customized using this configuration:

```php
<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

return [

    'entity_manager' => [
        'orm' => [
            'proxies_dir' => 'data/proxies', // Directory in which proxies will be persisted
            'default_repository_classname' => '', // A FQCN for the class used as repository by default
            'entities_mappings' => [ // List of directories from which entities mappings should be read
                __DIR__ . '/../foo/entities-mappings',
                __DIR__ . '/../bar/entities-mappings',
            ],
            'types' => [ // List of custom database types to map
                Doctrine\Type\ChronosDateTimeType::CHRONOS_DATETIME => Doctrine\Type\ChronosDateTimeType::class,
            ],
            'load_mappings_using_functional_style' => true, // Makes loader assume mappings return a function which should be invoked. Defaults to false
        ],
        'connection' => [ // Database connection params
            'driver' => 'pdo_mysql',
            'host' => 'shlink_db',
            'user' => 'DB_USER',
            'password' => 'DB_PASSWORD',
            'dbname' => 'DB_NAME',
            'charset' => 'utf8',
        ],
    ],

];
```

### Connections

As well as the EntityManager, there are two Connection objects that can be fetched.

* `Doctrine\DBAL\Connection`: Returns the connection used by the EntityManager, as is.
* `Shlinkio\Shlink\Common\Doctrine\NoDbNameConnection`: Returns a connection which is the same used by the EntityManager but without setting the database name. Useful to perform operations like creating the database (which would otherwise fail since the database does not exist yet).

## Logger

A few logger-related commodities are provided by this library.

### LoggerFactory

The `LoggerFactory` class is capable of creating `Monolog\Logger` instances based on the configuration described by [monolog-cascade](https://github.com/theorchard/monolog-cascade), which should be provided under the `logger` config entry.

This factory can create any logger registered in the configuration, but the service names used must follow the `Logger_<name>` pattern, where the `<name>` is the name used under the "loggers" config.

So, given this config:

```php
<?php

declare(strict_types=1);

return [

    'logger' => [
        'formatters' => [
            // ...
        ],
        'handlers' => [
            // ...
        ],
        'processors' => [
            // ...
        ],
        'loggers' => [
            'foo' => [],
            'bar' => [],
        ],
    ],

];
```

You should use the `Logger_foo` name to get the `foo` logger, and `Logger_bar` in order to get the `bar` one.

### Other logger utils

Besides the `LoggerFactory`, this module provides these utilities:

* `ExceptionWithNewLineProcessor`: A monolog processor which captures the `{e}` pattern inside log messages, and prepends a new line before it, assuming you are going to replace that with an exception trace.
* `LoggerAwareDelegatorFactory`: A zend-servicemanager delegator factory that checks if the service returned by previous factory is a `Psr\Log\LoggerAwareInterface` instance. If it is, it sets the `Psr\Log\LoggerInterface` service on it (if it was registered).
* `ErrorLogger`: A callable which expects a `Psr\Log\LoggerInterface` to be injected and uses it to log a `Throwable` when invoked. It will log 5xx errors with error level and 4xx errors with debug level.
* `ErrorHandlerListenerAttachingDelegator`: A zend-servicemanager delegator factory that registers all the services configured under `error_handler.listeners` as listeners for a stratigility `ErrorHandler` or a `ProblemDetailsMiddleware`.

## HTTP Client

A guzzle HTTP client comes preregistered, under the `GuzzleHttp\Client` service name, and aliased by `httpClient`.

It can be customized by adding request and response middlewares using a configuration like this:

```php
<?php

declare(strict_types=1);

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

return [

    'http_client' => [
        'request_middlewares' => [
            'some_service_middleware',
            fn (RequestInterface $req): RequestInterface => $req->withHeader('X-Foo', 'bar'),
        ],
        'response_middlewares' => [
            'some_service_middleware',
            fn (ResponseInterface $res): ResponseInterface => $res->withHeader('X-Foo', 'bar'),
        ],
    ],

];
```

Middlewares can be registered as static callbacks with a signature like the one from the example or as service names which resolve to a service with that same signature.

## Mercure

A helper to publish updates on a mercure hub comes preregistered. You need to provide a configuration like this one:

```php
<?php

declare(strict_types=1);

return [

    'mercure' => [

        // A URL publicly available in which the mercure hub can be reached.
        'public_hub_url' => null,

        // Optional. An internal URL in which the mercure hub can be reached. Will fall back to public_hub_url if not provided.
        'internal_hub_url' => null,

        // The JWT secret you provided to the mercure hub as JWT_KEY, so that valid JWTs can be generated.
        'jwt_secret' => null,

        // Optional. The issuer for generated JWTs. Will fall back to "Shlink".
        'jwt_issuer' => 'Shlink',
    ],

];
```

After that, you can get the publisher from the container, and invoke it to publish updates for specific topics:

```php
<?php

declare(strict_types=1);

use Symfony\Component\Mercure\Publisher;
use Symfony\Component\Mercure\Update;

$publisher = $container->get(Publisher::class);

$publisher(new Update('some_topic', json_encode([
    'foo' => 'bar',
])));
```

> Find more info about the symfony/mercure component here: https://symfony.com/blog/symfony-gets-real-time-push-capabilities

## Utils

* `PaginatorUtilsTrait`: A trait providing methods to get useful info from `Laminas\Paginator\Paginator` objects. It requires that you install `laminas/laminas-paginator`
* `PagerfantaUtilsTrait`: A trait providing methods to get useful info from `Pagerfanta\Pagerfanta` objects. It requires that you install `pagerfanta/core`
* `DateRange`: An immutable value object wrapping two `Chronos` date objects that can be used to represent a time period between two dates.
* `IpAddress`: An immutable value object representing an IP address that can be copied into an anonymized instance which removes the last octet.
