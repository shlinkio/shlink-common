<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use GuzzleHttp\Client as GuzzleClient;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Psr\Log\LoggerInterface;
use RKA\Middleware\IpAddress;
use Symfony\Component\Filesystem\Filesystem;

return [

    'dependencies' => [
        'factories' => [
            GuzzleClient::class => InvokableFactory::class,
            Filesystem::class => InvokableFactory::class,

            Middleware\CloseDbConnectionMiddleware::class => ConfigAbstractFactory::class,
            IpAddress::class => Middleware\IpAddressMiddlewareFactory::class,
            Logger\ErrorLogger::class => ConfigAbstractFactory::class,
        ],
        'aliases' => [
            'httpClient' => GuzzleClient::class,
        ],
        'abstract_factories' => [
            Factory\DottedAccessConfigAbstractFactory::class,
        ],
    ],

    ConfigAbstractFactory::class => [
        Middleware\CloseDbConnectionMiddleware::class => ['em'],
        Logger\ErrorLogger::class => [LoggerInterface::class],
    ],

];
