<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\RabbitMq;

use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Util\SSL;

class AMQPConnectionFactory
{
    public function __invoke(ContainerInterface $container): AMQPStreamConnection
    {
        $rabbitMqConfig = $container->get('config')['rabbitmq'] ?? [];
        $useSsl = $rabbitMqConfig['use_ssl'] ?? false;
        $connectionConfig = $this->connectionConfig();

        return $useSsl
            ? new AMQPSSLConnection(
                host: $rabbitMqConfig['host'],
                port: $rabbitMqConfig['port'],
                user: $rabbitMqConfig['user'],
                password: $rabbitMqConfig['password'],
                vhost: $rabbitMqConfig['vhost'],
                ssl_options: SSL::OPTIONS,
                config: $connectionConfig,
            )
            : new AMQPStreamConnection(
                host: $rabbitMqConfig['host'],
                port: $rabbitMqConfig['port'],
                user: $rabbitMqConfig['user'],
                password: $rabbitMqConfig['password'],
                vhost: $rabbitMqConfig['vhost'],
                // We have to pass the config as the ssl_protocol to avoid an internal deprecation warning
                // When the ssl_protocol is a config instance, it is internally set as config.
                // See https://github.com/php-amqplib/php-amqplib/blob/b4ade54ebe4685873f6316f9a05fc2c77a9e28f9/PhpAmqpLib/Connection/AMQPStreamConnection.php#L48-L55
                ssl_protocol: $connectionConfig, // @phpstan-ignore-line
            );
    }

    private function connectionConfig(): AMQPConnectionConfig
    {
        $config = new AMQPConnectionConfig();
        // Make sure we do not try to connect until the first time we need to access the server
        $config->setIsLazy(true);

        return $config;
    }
}
