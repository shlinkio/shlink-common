# Shlink Common

[![Build Status](https://img.shields.io/github/actions/workflow/status/shlinkio/shlink-common/ci.yml?branch=main&logo=github&style=flat-square)](https://github.com/shlinkio/shlink-common/actions/workflows/ci.yml?query=workflow%3A%22Continuous+integration%22)
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

This library provides both PSR-6 and PSR-16 cache adapters, via [symfony/cache](https://symfony.com/doc/current/components/cache.html).

They can be fetched via `Psr\Cache\CacheItemPoolInterface` and `Psr\SimpleCache\CacheInterface`.

The concrete implementation they return is different depending on your configuration:

* An `ArrayAdapter` instance when the `debug` config is set to true or when the APCu extension is not installed and the `cache.redis` config is not defined.
* An `ApcuAdapter`instance when no `cache.redis` is defined and the APCu extension is installed.
* A `RedisAdapter` instance when the `cache.redis` config is defined.

The last two adapters will use the namespace defined in `cache.namespace` config entry.

The three of them will allow setting a default lifetime for those entries which do not explicitly define one, picking it up from `cache.default_lifetime`.

```php
<?php

declare(strict_types=1);

return [

    'debug' => false,

    'cache' => [
        'namespace' => 'my_namespace',
        'default_lifetime' => 86400, // Optional. Defaults to "never expire"

        'redis' => [
            'servers' => [
                // These should be valid URIs. Make sure credentials are URL-encoded
                'tcp://1.1.1.1:6379',
                'tcp://2.2.2.2:6379',
                'tcp://3.3.3.3:6379?database=3', // Define a database index (https://redis.io/docs/commands/select/)
                'tcp://user:pass%40word@4.4.4.4:6379', // Redis ACL (https://redis.io/docs/latest/operate/oss_and_stack/management/security/acl/)
                'tcp://:password@5.5.5.5:6379', // Redis security (https://redis.io/docs/latest/operate/oss_and_stack/management/security/)
                'tls://server_with_encryption:6379',
                'unix://localhost/run/redis/redis-server.sock', // Connect via unix socket
                'unix://localhost/run/redis/redis-server.sock?database=2', // Connect via unix socket and define database index
            ],
            'sentinel_service' => 'my_master', // Optional.
            'username' => 'user', // Optional. Ignored if no `sentinel_service` is set.
            'password' => 'my_password', // Optional. Ignored if no `sentinel_service` is set.
        ],
    ],

];
```

### Redis support

You can allow caching to be done on a redis instance, redis cluster or redis sentinels, by defining some options under `cache.redis` config.

* `servers`: A list of redis servers. If one is provided, it will be treated as a single instance, and otherwise, a cluster will be assumed.
* `sentinel_service`: Lets you enable sentinel mode by providing the master/service name. When provided, the servers will be treated as sentinel instances, not the redis cluster instances.
* `username`: When `sentinel_service` is set, it lets you define the username for the redis cluster instances when using ACL. For non-sentinel contexts, you should provide the username as part of the server URL directly.
* `password`: When `sentinel_service` is set, it lets you define the password for the redis cluster instances. For non-sentinel contexts, you should provide the password as part of the server URL directly.

### Redis publishing helper

Also, in order to support publishing in redis pub/sub, a `RedisPublishingHelper` service is provided, which will use the configuration above in order to connect to the redis instance/cluster.

```php
<?php

declare(strict_types=1);

use Shlinkio\Shlink\Common\Cache\RedisPublishingHelper;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;

$helper = $container->get(RedisPublishingHelper::class);

$helper->publishUpdate(Update::forTopicAndPayload('some_queue', ['foo' => 'bar']));
```

## Middlewares

This module provides a set of useful middlewares, all registered as services in the container:

* `CloseDbConnectionMiddleware`:

    Should be an early middleware in the pipeline. It makes use of the EntityManager that ensure the database connection is closed at the end of the request.

    It should be used when serving an app with a non-blocking IO server (like RoadRunner or FrankenPHP), which persist services between requests.

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

* `RequestIdMiddleware`: Sets a `request-id` attribute to current request, by reading the `X-Request-Id` header or falling back to an auto-generated UUID v4.

    It also implements monolog's `ProcessorInterface` to set the request ID as `extra.request-id`.

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

use Doctrine\ORM\Events;

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
            'listeners' => [ // Map telling which service listeners to invoke for every ORM event
                Events::postFlush => ['some_service'],
                Events::preUpdate => ['foo', 'bar'],
            ]
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

### EntityRepository factory

In order to allow multiple repositories per entity, and also to avoid the `$this->em->getRepository(MyEntity::class)` pattern and instead "promote" injecting repositories, this library provides a `EntityRepositoryFactory` helper class that can be used like this.

```php
<?php

declare(strict_types=1);

use Shlinkio\Shlink\Common\Doctrine\EntityRepositoryFactory;

return [

    'dependencies' => [
        MyEntityRepository::class => [EntityRepositoryFactory::class, MyEntity::class],
    ],

];
```

## Logger

A few logger-related helpers are provided by this library.

### LoggerFactory

The `LoggerFactory` class is capable of creating `Monolog\Logger` instances wrapping either stream handlers or rotating file handlers, which should be defined under the `logger` config entry.

```php
<?php

declare(strict_types=1);

use Monolog\Level;
use Shlinkio\Shlink\Common\Middleware\RequestIdMiddleware;
use Shlinkio\Shlink\Common\Logger\LoggerFactory;
use Shlinkio\Shlink\Common\Logger\LoggerType;

return [

    'logger' => [
        'Shlink' => [
            'type' => LoggerType::FILE->value,
            'level' => Level::Info->value,
            'processors' => [RequestIdMiddleware::class],
            'formatter' => [
                'type' => 'console', // 'console' or 'json'. Defaults to 'console'
                // If 'console' type is defined, you can define the line format
                'line_format' => '[%datetime%] [%extra.request_id%] %channel%.%level_name% - %message%',
            ],
        ],
        'Access' => [
            'type' => LoggerType::STREAM->value,
            'level' => Level::Alert->value,
            'formatter' => [
                'line_format' => '[%datetime%] %level_name% - %message%',
                'add_new_line' => false,
            ],
        ],
    ],

    'dependencies' => [
        'factories' => [
            'ShlinkLogger' => [LoggerFactory::class, 'Shlink'],
            'AccessLogger' => [LoggerFactory::class, 'Access'],
        ],
    ],

];
```

Every logger can have these config options:

* `type`: Any value from the `LoggerType` enum, which will make different handlers to be injected in the logger instance.
* `level`: Any value from monolog's `Level` enum, which determines the minimum level of the generated logs. Defaults to `Level::Info` if not provided.
* `line_format`: The format of the line logs to generate.
* `add_new_line`: Whether to add an extra empty line on every log. Defaults to `true`.
* `processors`: An optional list of extra processors to inject in the generated logger. The values in the array must be service names.
* `destination`: Where to send logs. It defaults to `php:stdout` for stream logs, and `data/log/shlink_log.log` for file logs.

### Other logger utils

This module provides some other logger-related utilities:

* `ExceptionWithNewLineProcessor`: A monolog processor which captures the `{e}` pattern inside log messages, and prepends a new line before it, assuming you are going to replace that with an exception trace.
* `LoggerAwareDelegatorFactory`: A ServiceManager delegator factory that checks if the service returned by previous factory is a `Psr\Log\LoggerAwareInterface` instance. If it is, it sets the `Psr\Log\LoggerInterface` service on it (if it was registered).
* `ErrorLogger`: A callable which expects a `Psr\Log\LoggerInterface` to be injected and uses it to log a `Throwable` when invoked. It will log 5xx errors with error level and 4xx errors with debug level.
* `ErrorHandlerListenerAttachingDelegator`: A ServiceManager delegator factory that registers all the services configured under `error_handler.listeners` as listeners for a stratigility `ErrorHandler` or a `ProblemDetailsMiddleware`.
* `BackwardsCompatibleMonologProcessor`: It lets you wrap monolog 2 processors with `callable(array): array` signature to make them compatible with monolog 3 and its new `callable(LogRecord): LogRecord` signature.
* `AccessLogMiddleware`: A PSR-15 middleware which logs requests. It expects a PSR-3 logger service to be registered under `AccessLogMiddleware::LOGGER_SERVICE_NAME`.

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
        // Whether the integration with mercure is enabled or not.
        // If not explicitly set, the integration is considered enabled if a public URL is set, but next major version
        // will change the default to false.
        'enabled' => true,

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

## RabbitMQ

A helper to publish updates on RabbitMQ comes preregistered. You need to provide a configuration like this one:

```php
<?php

declare(strict_types=1);

return [

    'rabbitmq' => [
        // The RabbitMQ server name
        'host' => 'my-rabbitmq-server.com',

        // The RabbitMQ server port
        'port' => '5672',

        // The username credential
        'user' => 'username',

        // The password credential
        'password' => 'password',

        // The vHost
        'vhost' => '/',

        // Tells if connection should be encrypted. Defaults to false if not provided
        'use_ssl' => true,
    ],

];
```

After that, you can get the helper from the container, and invoke it to publish updates for specific queues:

```php
<?php

declare(strict_types=1);

use Shlinkio\Shlink\Common\RabbitMq\RabbitMqPublishingHelper;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;

$helper = $container->get(RabbitMqPublishingHelper::class);

$helper->publishUpdate(Update::forTopicAndPayload('some_queue', ['foo' => 'bar']));
```

## Utils

* `PagerfantaUtilsTrait`: A trait providing methods to get useful info from `Pagerfanta\Pagerfanta` objects. It requires that you install `pagerfanta/core`.
* `Paginator`: An object extending `Pagerfanta`, that makes it behave as laminas' Paginator object on regards to be able to set `-1` as the max results and get all the results in that case. It requires that you install `pagerfanta/core`.
* `DateRange`: An immutable value object wrapping two `Chronos` date objects that can be used to represent a time period between two dates.
* `IpAddress`: An immutable value object representing an IP address that can be copied into an anonymized instance which removes the last octet.
* `NamespacedStore`: A `symfony/lock` store that can wrap another store instance but making sure keys are prefixed with a namespace and namespace separator.
