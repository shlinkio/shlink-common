<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Cache;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\Connection\Cluster\ClusterInterface;
use Predis\Connection\Cluster\PredisCluster;
use Predis\Connection\Cluster\RedisCluster;
use Predis\Connection\Factory;
use Predis\Connection\Replication\MasterSlaveReplication;
use Predis\Connection\Replication\ReplicationInterface;
use Predis\Connection\Replication\SentinelReplication;
use Psr\Container\ContainerInterface;
use ReflectionObject;
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
        yield 'cluster of sentinels with password' => [[
            'servers' => ['tcp://1.1.1.1:26379', 'tcp://2.2.2.2:26379'],
            'sentinel_service' => 'foo',
            'password' => 'foo',
        ], PredisCluster::class, SentinelReplication::class];
        yield 'cluster of sentinels with ACL' => [[
            'servers' => ['tcp://1.1.1.1:26379', 'tcp://2.2.2.2:26379'],
            'sentinel_service' => 'foo',
            'username' => 'user',
            'password' => 'pass',
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
        string|null $expectedUsername = null,
        string|null $expectedPassword = null,
        array|null $expectedSslOptions = null,
        string|null $expectedPath = null,
    ): void {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'cache' => ['redis' => $redisConfig],
        ]);

        $client = ($this->factory)($this->container);
        $conn = $client->getConnection();

        self::assertEquals($expectedUsername, $conn->getParameters()->username); // @phpstan-ignore-line
        self::assertEquals($expectedPassword, $conn->getParameters()->password); // @phpstan-ignore-line
        self::assertEquals($expectedSslOptions, $conn->getParameters()->ssl); // @phpstan-ignore-line
        self::assertEquals($expectedPath, $conn->getParameters()->path); // @phpstan-ignore-line
    }

    public static function provideServersWithCredentials(): iterable
    {
        yield 'no credentials' => [[
            'servers' => ['tcp://1.1.1.1:6379'],
        ]];
        yield 'username and password' => [[
            'servers' => ['tcp://foo:bar@1.1.1.1:6379'],
        ], 'expectedUsername' => 'foo', 'expectedPassword' => 'bar'];
        yield 'password only' => [[
            'servers' => ['tcp://:baz@1.1.1.1:6379'],
        ], 'expectedPassword' => 'baz'];
        yield 'username only' => [[
            'servers' => ['tcp://foo@1.1.1.1:6379'],
        ], 'expectedUsername' => 'foo'];
        yield 'URL-encoded' => [[
            'servers' => ['tcp://user%3Aname:pass%40word@1.1.1.1:6379'],
        ], 'expectedUsername' => 'user:name', 'expectedPassword' => 'pass@word'];
        yield 'tls encryption' => [[
            'servers' => ['tls://1.1.1.1:6379'],
        ], 'expectedSslOptions' => SSL::OPTIONS];
        yield 'rediss encryption' => [[
            'servers' => ['rediss://1.1.1.1:6379'],
        ], 'expectedSslOptions' => SSL::OPTIONS];
        yield 'unix socket' => [[
            'servers' => ['unix:/path/to/redis.sock'],
        ], 'expectedPath' => '/path/to/redis.sock'];
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
            'servers' => ['tcp://1.1.1.1:6379?database=5'],
        ], 5];
    }

    #[Test]
    #[TestWith(['foo'])]
    #[TestWith(['1.2'])]
    public function exceptionIsThrownIfDatabaseIsNotAnInt(string $database): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'cache' => ['redis' => [
                'servers' => ['tcp://1.1.1.1:6379?database=' . $database],
            ]],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The redis database index should be an integer, ' . $database . ' provided');

        ($this->factory)($this->container);
    }

    #[Test]
    #[TestWith(['my_password', null])]
    #[TestWith(['my_password', 'my_username'])]
    public function parametersAreSetWhenSentinelServiceAndPasswordAreProvided(
        string $password,
        string|null $username,
    ): void {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'cache' => [
                'redis' => [
                    'servers' => ['tcp://1.1.1.1:26379', 'tcp://2.2.2.2:26379'],
                    'sentinel_service' => 'my_server',
                    'password' => $password,
                    'username' => $username,
                ],
            ],
        ]);

        $client = ($this->factory)($this->container);
        /** @var SentinelReplication $conn */
        $conn = $client->getConnection();
        $ref = new ReflectionObject($conn);
        $connFactoryRef = $ref->getProperty('connectionFactory');
        /** @var Factory $connFactory */
        $connFactory = $connFactoryRef->getValue($conn);
        $defaultParams = $connFactory->getDefaultParameters();

        self::assertEquals($password, $defaultParams['password']);
        self::assertEquals($username, $defaultParams['username']);
    }
}
