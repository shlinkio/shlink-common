<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\RabbitMq;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPConnectionFactory as ConnFactory;
use Psr\Container\ContainerInterface;

class AMQPConnectionFactory
{
    public function __invoke(ContainerInterface $container): AbstractConnection
    {
        $connectionConfig = $container->get(AMQPConnectionConfig::class);
        return ConnFactory::create($connectionConfig);
    }
}
