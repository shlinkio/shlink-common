<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\RabbitMq;

use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\RabbitMq\AMQPConnectionFactory;

class AMQPConnectionFactoryTest extends TestCase
{
    private AMQPConnectionFactory $factory;
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->factory = new AMQPConnectionFactory();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    #[Test]
    public function connectionIsCreatedWithConfig(): void
    {
        $expectedConfig = new AMQPConnectionConfig();
        $expectedConfig->setIsLazy(true); // Avoid connection to be attempted

        $this->container->expects($this->once())->method('get')->willReturn($expectedConfig);
        ($this->factory)($this->container);
    }
}
