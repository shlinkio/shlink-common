<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use GuzzleHttp\Client as GuzzleClient;
use RKA\Middleware\IpAddress;
use Symfony\Component\Filesystem\Filesystem;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

return [

    'dependencies' => [
        'factories' => [
            GuzzleClient::class => InvokableFactory::class,
            Filesystem::class => InvokableFactory::class,

            Middleware\CloseDbConnectionMiddleware::class => ConfigAbstractFactory::class,
            IpAddress::class => Middleware\IpAddressMiddlewareFactory::class,
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
    ],

];
