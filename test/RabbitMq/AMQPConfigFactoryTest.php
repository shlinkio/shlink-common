<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\RabbitMq;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\RabbitMq\AMQPConfigFactory;

class AMQPConfigFactoryTest extends TestCase
{
    private AMQPConfigFactory $factory;
    private MockObject & ContainerInterface $container;

    protected function setUp(): void
    {
        $this->factory = new AMQPConfigFactory();
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

        $config = ($this->factory)($this->container);

        self::assertEquals($useSsl['use_ssl'] ?? false, $config->isSecure());
        self::assertEquals('host', $config->getHost());
        self::assertEquals(1111, $config->getPort());
        self::assertEquals('user', $config->getUser());
        self::assertEquals('password', $config->getPassword());
        self::assertEquals('vhost', $config->getVhost());
    }

    public static function provideSsl(): iterable
    {
        yield 'no ssl set' => [[]];
        yield 'no ssl' => [['use_ssl' => false]];
        yield 'ssl' => [['use_ssl' => true]];
    }
}
