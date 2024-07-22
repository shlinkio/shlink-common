<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\RabbitMq;

use PhpAmqpLib\Connection\AMQPConnectionConfig;
use Psr\Container\ContainerInterface;

class AMQPConfigFactory
{
    public function __invoke(ContainerInterface $container): AMQPConnectionConfig
    {
        $rabbitMqConfig = $container->get('config')['rabbitmq'] ?? [];

        $config = new AMQPConnectionConfig();
        $config->setHost($rabbitMqConfig['host']);
        $config->setPort($rabbitMqConfig['port']);
        $config->setUser($rabbitMqConfig['user']);
        $config->setPassword($rabbitMqConfig['password']);
        $config->setVhost($rabbitMqConfig['vhost']);
        $config->setIsSecure($rabbitMqConfig['use_ssl'] ?? false);

        // Make sure we do not try to connect until the first time we need to access the server
        $config->setIsLazy(true);

        return $config;
    }
}
