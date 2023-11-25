<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\RabbitMq;

use PhpAmqpLib\Connection\AMQPSSLConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\RabbitMq\AMQPConnectionFactory;

class AMQPConnectionFactoryTest extends TestCase
{
    private AMQPConnectionFactory $factory;
    private MockObject & ContainerInterface $container;

    protected function setUp(): void
    {
        $this->factory = new AMQPConnectionFactory();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    #[Test, DataProvider('provideSsl')]
    public function properConnectionObjectIsCreated(array $useSsl): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'rabbitmq' => [
                ...$useSsl,
                'host' => 'host',
                'port' => 1111,
                'user' => 'user',
                'password' => 'password',
                'vhost' => 'vhost',
            ],
        ]);

        $connection = ($this->factory)($this->container);

        self::assertEquals($useSsl['use_ssl'] ?? false, $connection instanceof AMQPSSLConnection);
    }

    public static function provideSsl(): iterable
    {
        yield 'no ssl set' => [[]];
        yield 'no ssl' => [['use_ssl' => false]];
        yield 'ssl' => [['use_ssl' => true]];
    }
}
