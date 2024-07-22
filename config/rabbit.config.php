<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPStreamConnection;

return [

    'rabbitmq' => [
        'use_ssl' => false,
    ],

    'dependencies' => [
        'factories' => [
            AMQPStreamConnection::class => RabbitMq\AMQPConnectionFactory::class,
            AMQPConnectionConfig::class => RabbitMq\AMQPConfigFactory::class,
            RabbitMq\RabbitMqPublishingHelper::class => ConfigAbstractFactory::class,
        ],
        'delegators' => [
            AMQPStreamConnection::class => [
                LazyServiceFactory::class,
            ],
        ],
        'lazy_services' => [
            'class_map' => [
                AMQPStreamConnection::class => AMQPStreamConnection::class,
            ],
        ],
    ],

    ConfigAbstractFactory::class => [
        RabbitMq\RabbitMqPublishingHelper::class => [AMQPStreamConnection::class],
    ],

];
