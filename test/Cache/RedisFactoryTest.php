<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Cache;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\Connection\Cluster\ClusterInterface;
use Predis\Connection\Cluster\PredisCluster;
use Predis\Connection\Cluster\RedisCluster;
use Predis\Connection\Replication\MasterSlaveReplication;
use Predis\Connection\Replication\ReplicationInterface;
use Predis\Connection\Replication\SentinelReplication;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Cache\RedisFactory;
use Shlinkio\Shlink\Common\Exception\InvalidArgumentException;

class RedisFactoryTest extends TestCase
{
    private RedisFactory $factory;
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = new RedisFactory();
    }

    /**
     * @param class-string<ClusterInterface> $expectedCluster
     * @param class-string<ReplicationInterface> $expectedReplication
     * @test
     * @dataProvider provideRedisConfig
     */
    public function createsRedisClientBasedOnCacheConfig(
        ?array $config,
        string $expectedCluster,
        string $expectedReplication,
    ): void {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'cache' => [
                'redis' => $config,
            ],
        ]);

        $client = ($this->factory)($this->container);

        self::assertInstanceOf($expectedCluster, ($client->getOptions()->cluster)());
        self::assertInstanceOf($expectedReplication, ($client->getOptions()->replication)([]));
    }

    public function provideRedisConfig(): iterable
    {
        yield 'no config' => [null, PredisCluster::class, MasterSlaveReplication::class];
        yield 'single server as string' => [[
            'servers' => 'tcp://127.0.0.1:6379',
        ], PredisCluster::class, MasterSlaveReplication::class];
        yield 'single server as string with password' => [[
            'servers' => 'tcp://password:127.0.0.1:6379',
        ], PredisCluster::class, MasterSlaveReplication::class];
        yield 'single server as array' => [[
            'servers' => ['tcp://127.0.0.1:6379'],
        ], PredisCluster::class, MasterSlaveReplication::class];
        yield 'cluster of servers' => [[
            'servers' => ['tcp://1.1.1.1:6379', 'tcp://2.2.2.2:6379'],
        ], RedisCluster::class, MasterSlaveReplication::class];
        yield 'cluster of servers with spaces' => [[
            'servers' => ['tcp://1.1.1.1:6379  ', '  tcp://2.2.2.2:6379 '],
        ], RedisCluster::class, MasterSlaveReplication::class];
        yield 'empty cluster of servers' => [[
            'servers' => [],
        ], PredisCluster::class, MasterSlaveReplication::class];
        yield 'cluster of servers as string' => [[
            'servers' => 'tcp://1.1.1.1:6379,tcp://2.2.2.2:6379',
        ], RedisCluster::class, MasterSlaveReplication::class];
        yield 'cluster of servers as string with spaces' => [[
            'servers' => 'tcp://1.1.1.1:6379, tcp://2.2.2.2:6379 , tcp://3.3.3.3:6379',
        ], RedisCluster::class, MasterSlaveReplication::class];
        yield 'cluster of sentinels' => [[
            'servers' => ['tcp://1.1.1.1:6379', 'tcp://2.2.2.2:6379'],
            'sentinel_service' => 'foo',
        ], PredisCluster::class, SentinelReplication::class];
        yield 'cluster of sentinels with ACL' => [[
            'servers' => ['tcp://foo:bar@1.1.1.1:6379', 'tcp://foo2:bar2@2.2.2.2:6379'],
            'sentinel_service' => 'foo',
        ], PredisCluster::class, SentinelReplication::class];
    }

    /** @test */
    public function exceptionIsThrownIfServerUriHasInvalidFormat(): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'cache' => [
                'redis' => ['servers' => ['//']],
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Provided server "//" is not a valid URL with format schema://[[username:]password@]host:port',
        );

        ($this->factory)($this->container);
    }

    /**
     * @test
     * @dataProvider provideServersWithCredentials
     */
    public function providedCredentialsArePassedToConnection(
        string $server,
        ?string $expectedUsername,
        ?string $expectedPassword,
    ): void {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'cache' => [
                'redis' => ['servers' => [$server]],
            ],
        ]);

        $client = ($this->factory)($this->container);
        $conn = $client->getConnection();

        self::assertEquals($expectedUsername, $conn->getParameters()->username); // @phpstan-ignore-line
        self::assertEquals($expectedPassword, $conn->getParameters()->password); // @phpstan-ignore-line
    }

    public function provideServersWithCredentials(): iterable
    {
        yield 'no credentials' => ['tcp://1.1.1.1:6379', null, null];
        yield 'password only' => ['tcp://foo:bar@1.1.1.1:6379', 'foo', 'bar'];
        yield 'username and password' => ['tcp://foo@1.1.1.1:6379', null, 'foo'];
    }
}
