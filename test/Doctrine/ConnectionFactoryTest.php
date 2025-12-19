<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Doctrine\ConnectionFactory;

class ConnectionFactoryTest extends TestCase
{
    private ConnectionFactory $factory;
    private MockObject & EntityManagerInterface $em;
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->factory = new ConnectionFactory();
    }

    #[Test]
    public function properServiceFallbackOccursWhenInvoked(): void
    {
        $connection = $this->createStub(Connection::class);
        $this->em->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->container->expects($this->once())->method('get')->with(EntityManager::class)->willReturn($this->em);

        $result = ($this->factory)($this->container);

        self::assertSame($connection, $result);
    }
}
