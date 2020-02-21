<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Psr\Log\LoggerInterface;
use RKA\Middleware\IpAddress;
use Symfony\Component\Filesystem\Filesystem;

return [

    'dependencies' => [
        'factories' => [
            Filesystem::class => InvokableFactory::class,

            Middleware\CloseDbConnectionMiddleware::class => ConfigAbstractFactory::class,
            IpAddress::class => Middleware\IpAddressMiddlewareFactory::class,
            Logger\ErrorLogger::class => ConfigAbstractFactory::class,
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
