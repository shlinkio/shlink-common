<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\RabbitMq\RabbitMqPublishingHelper;

return [

    'rabbitmq' => [],

    'dependencies' => [
        'factories' => [
            AMQPConnectionConfig::class => static function () {
                $config = new AMQPConnectionConfig();
                // Make sure we do not try to connect until the first time we need to access the server
                $config->setIsLazy(true);

                return $config;
            },
            AMQPStreamConnection::class => static fn (ContainerInterface $c) => new AMQPStreamConnection(
                host: $c->get('config.rabbitmq.host'),
                port: $c->get('config.rabbitmq.port'),
                user: $c->get('config.rabbitmq.user'),
                password: $c->get('config.rabbitmq.password'),
                vhost: $c->get('config.rabbitmq.vhost'),
                config: $c->get(AMQPConnectionConfig::class),
            ),
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
        RabbitMqPublishingHelper::class => [AMQPStreamConnection::class],
    ],

];
