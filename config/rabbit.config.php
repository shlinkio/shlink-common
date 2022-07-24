<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Shlinkio\Shlink\Common\RabbitMq\RabbitMqPublishingHelper;

return [

    'rabbitmq' => [],

    'dependencies' => [
        'factories' => [
            AMQPStreamConnection::class => ConfigAbstractFactory::class,
            RabbitMqPublishingHelper::class => ConfigAbstractFactory::class,
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
        AMQPStreamConnection::class => [
            'config.rabbitmq.host',
            'config.rabbitmq.port',
            'config.rabbitmq.user',
            'config.rabbitmq.password',
            'config.rabbitmq.vhost',
        ],

        RabbitMqPublishingHelper::class => [AMQPStreamConnection::class],
    ],

];
