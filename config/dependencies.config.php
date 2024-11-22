<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Psr\Log\LoggerInterface;

return [

    'dependencies' => [
        'factories' => [
            Middleware\RequestIdMiddleware::class => InvokableFactory::class,
            Middleware\CloseDbConnectionMiddleware::class => ConfigAbstractFactory::class,
            Middleware\ContentLengthMiddleware::class => InvokableFactory::class,
            Middleware\AccessLogMiddleware::class => ConfigAbstractFactory::class,

            Logger\ErrorLogger::class => ConfigAbstractFactory::class,
        ],
    ],

    ConfigAbstractFactory::class => [
        Middleware\CloseDbConnectionMiddleware::class => ['em'],
        Middleware\AccessLogMiddleware::class => [Middleware\AccessLogMiddleware::LOGGER_SERVICE_NAME],
        Logger\ErrorLogger::class => [LoggerInterface::class],
    ],

];
