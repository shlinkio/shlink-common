<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use GuzzleHttp\Client as GuzzleClient;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
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

            'logger' => LoggerInterface::class,
            Logger::class => 'Logger_Shlink',
            LoggerInterface::class => 'Logger_Shlink',
        ],
        'abstract_factories' => [
            Factory\DottedAccessConfigAbstractFactory::class,
        ],
    ],

    ConfigAbstractFactory::class => [
        Middleware\CloseDbConnectionMiddleware::class => ['em'],
    ],

];
