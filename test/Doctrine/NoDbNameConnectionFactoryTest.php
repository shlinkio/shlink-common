<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Doctrine\NoDbNameConnectionFactory;

class NoDbNameConnectionFactoryTest extends TestCase
{
    private NoDbNameConnectionFactory $factory;
    private MockObject & Connection $originalConn;
    private MockObject & ContainerInterface $container;
    private MockObject & EntityManager $em;

    protected function setUp(): void
    {
        $this->originalConn = $this->createMock(Connection::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->em = $this->createMock(EntityManager::class);

        $this->factory = new NoDbNameConnectionFactory();
    }

    #[Test]
    public function newConnectionIsCreatedRemovingDbNameFromOriginalConnectionParams(): void
    {
        $params = [
            'username' => 'foo',
            'password' => 'bar',
            'dbname' => 'something',
        ];
        $this->originalConn->expects($this->once())->method('getParams')->willReturn($params);
        $this->originalConn->expects($this->once())->method('getDriver')->willReturn($this->createMock(Driver::class));
        $this->originalConn->expects($this->once())->method('getConfiguration')->willReturn(null);
        $this->em->expects($this->once())->method('getEventManager')->willReturn(null);
        $this->em->expects($this->once())->method('getConnection')->willReturn($this->originalConn);
        $this->container->expects($this->once())->method('get')->with(EntityManager::class)->willReturn($this->em);

        $conn = ($this->factory)($this->container);

        self::assertEquals([
            'username' => 'foo',
            'password' => 'bar',
        ], $conn->getParams());
    }
}
