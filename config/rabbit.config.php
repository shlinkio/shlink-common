<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\RabbitMq\RabbitMqPublishingHelper;
use Shlinkio\Shlink\Common\Util\SSL;

return [

    'rabbitmq' => [
        'use_ssl' => false,
    ],

    'dependencies' => [
        'factories' => [
            AMQPConnectionConfig::class => static function () {
                $config = new AMQPConnectionConfig();
                // Make sure we do not try to connect until the first time we need to access the server
                $config->setIsLazy(true);

                return $config;
            },
            AMQPStreamConnection::class => static function (ContainerInterface $c): AMQPStreamConnection {
                $useSsl = $c->get('config.rabbitmq.use_ssl');
                $connectionConfig = $c->get(AMQPConnectionConfig::class);

                return $useSsl
                    ? new AMQPSSLConnection(
                        host: $c->get('config.rabbitmq.host'),
                        port: $c->get('config.rabbitmq.port'),
                        user: $c->get('config.rabbitmq.user'),
                        password: $c->get('config.rabbitmq.password'),
                        vhost: $c->get('config.rabbitmq.vhost'),
                        ssl_options: SSL::OPTIONS,
                        config: $connectionConfig,
                    )
                    : new AMQPStreamConnection(
                        host: $c->get('config.rabbitmq.host'),
                        port: $c->get('config.rabbitmq.port'),
                        user: $c->get('config.rabbitmq.user'),
                        password: $c->get('config.rabbitmq.password'),
                        vhost: $c->get('config.rabbitmq.vhost'),
                        // We have to pass the config as the ssl_protocol to avoid an internal deprecation warning
                        // When the ssl_protocol is a config instance, it is internally set as config.
                        // See https://github.com/php-amqplib/php-amqplib/blob/b4ade54ebe4685873f6316f9a05fc2c77a9e28f9/PhpAmqpLib/Connection/AMQPStreamConnection.php#L48-L55
                        ssl_protocol: $connectionConfig,
                    );
            },
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
