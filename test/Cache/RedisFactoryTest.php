<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Cache;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
use Shlinkio\Shlink\Common\Util\SSL;

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
     */
    #[Test, DataProvider('provideRedisConfig')]
    public function createsRedisClientBasedOnCacheConfig(
        array|null $config,
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

    public static function provideRedisConfig(): iterable
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
        yield 'single sentinel as string' => [[
            'servers' => 'tcp://1.1.1.1:26379',
            'sentinel_service' => 'foo',
        ], PredisCluster::class, SentinelReplication::class];
        yield 'single sentinel as array' => [[
            'servers' => ['tcp://1.1.1.1:26379'],
            'sentinel_service' => 'foo',
        ], PredisCluster::class, SentinelReplication::class];
        yield 'cluster of sentinels' => [[
            'servers' => ['tcp://1.1.1.1:26379', 'tcp://2.2.2.2:26379'],
            'sentinel_service' => 'foo',
        ], PredisCluster::class, SentinelReplication::class];
        yield 'cluster of sentinels with ACL' => [[
            'servers' => ['tcp://foo:bar@1.1.1.1:26379', 'tcp://foo2:bar2@2.2.2.2:26379'],
            'sentinel_service' => 'foo',
        ], PredisCluster::class, SentinelReplication::class];
    }

    #[Test]
    public function exceptionIsThrownIfServerUriHasInvalidFormat(): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'cache' => [
                'redis' => ['servers' => ['//']],
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Provided server "//" is not a valid URL with format schema://[[username]:password@]host:port',
        );

        ($this->factory)($this->container);
    }

    #[Test, DataProvider('provideServersWithCredentials')]
    public function providedCredentialsArePassedToConnection(
        array $redisConfig,
        string|null $expectedUsername,
        string|null $expectedPassword,
        array|null $expectedSslOptions,
    ): void {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'cache' => ['redis' => $redisConfig],
        ]);

        $client = ($this->factory)($this->container);
        $conn = $client->getConnection();

        self::assertEquals($expectedUsername, $conn->getParameters()->username); // @phpstan-ignore-line
        self::assertEquals($expectedPassword, $conn->getParameters()->password); // @phpstan-ignore-line
        self::assertEquals($expectedSslOptions, $conn->getParameters()->ssl); // @phpstan-ignore-line
    }

    public static function provideServersWithCredentials(): iterable
    {
        yield 'no credentials' => [[
            'servers' => ['tcp://1.1.1.1:6379'],
        ], null, null, null];
        yield 'username and password' => [[
            'servers' => ['tcp://foo:bar@1.1.1.1:6379'],
        ], 'foo', 'bar', null];
        yield 'password only' => [[
            'servers' => ['tcp://:baz@1.1.1.1:6379'],
        ], null, 'baz', null];
        yield 'username only' => [[
            'servers' => ['tcp://foo@1.1.1.1:6379'],
        ], 'foo', null, null];
        yield 'URL-encoded' => [[
            'servers' => ['tcp://user%3Aname:pass%40word@1.1.1.1:6379'],
        ], 'user:name', 'pass@word', null];
        yield 'tls encryption' => [[
            'servers' => ['tls://1.1.1.1:6379'],
        ], null, null, SSL::OPTIONS];
        yield 'rediss encryption' => [[
            'servers' => ['rediss://1.1.1.1:6379'],
        ], null, null, SSL::OPTIONS];
    }

    #[Test, DataProvider('provideServersWithDatabases')]
    public function databaseConfigurationIsApplied(
        array $redisConfig,
        int|null $expectedDatabase,
    ): void {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'cache' => ['redis' => $redisConfig],
        ]);

        $client = ($this->factory)($this->container);
        $conn = $client->getConnection();

        self::assertEquals($expectedDatabase, $conn->getParameters()->database); // @phpstan-ignore-line
    }

    public static function provideServersWithDatabases(): iterable
    {
        yield 'no database' => [[
            'servers' => ['tcp://1.1.1.1:6379'],
        ], null];
        yield 'database' => [[
            'servers' => ['tcp://1.1.1.1:6379/5'],
        ], 5];
    }
}
