<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Psr\Log\LoggerInterface;
use RKA\Middleware\IpAddress;
use Shlinkio\Shlink\Config\Factory\SwooleInstalledFactory;
use Symfony\Component\Filesystem\Filesystem;

return [

    'dependencies' => [
        'factories' => [
            Filesystem::class => InvokableFactory::class,

            Middleware\CloseDbConnectionMiddleware::class => ConfigAbstractFactory::class,
            Middleware\ContentLengthMiddleware::class => ConfigAbstractFactory::class,
            IpAddress::class => Middleware\IpAddressMiddlewareFactory::class,

            Logger\ErrorLogger::class => ConfigAbstractFactory::class,
        ],
    ],

    ConfigAbstractFactory::class => [
        Middleware\CloseDbConnectionMiddleware::class => ['em'],
        Middleware\ContentLengthMiddleware::class => [SwooleInstalledFactory::SWOOLE_INSTALLED],
        Logger\ErrorLogger::class => [LoggerInterface::class],
    ],

];
