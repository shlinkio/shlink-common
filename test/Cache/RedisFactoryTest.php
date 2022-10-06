<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Cache;

use PHPUnit\Framework\TestCase;
use Predis\Connection\Cluster\PredisCluster;
use Predis\Connection\Cluster\RedisCluster;
use Predis\Connection\Replication\MasterSlaveReplication;
use Predis\Connection\Replication\SentinelReplication;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Cache\RedisFactory;

class RedisFactoryTest extends TestCase
{
    use ProphecyTrait;

    private RedisFactory $factory;
    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new RedisFactory();
    }

    /**
     * @test
     * @dataProvider provideRedisConfig
     */
    public function createsRedisClientBasedOnCacheConfig(
        ?array $config,
        string $expectedCluster,
        string $expectedReplication,
    ): void {
        $getConfig = $this->container->get('config')->willReturn([
            'cache' => [
                'redis' => $config,
            ],
        ]);

        $client = ($this->factory)($this->container->reveal());

        $getConfig->shouldHaveBeenCalledOnce();

        self::assertInstanceOf($expectedCluster, ($client->getOptions()->cluster)());
        self::assertInstanceOf($expectedReplication, ($client->getOptions()->replication)([]));
    }

    public function provideRedisConfig(): iterable
    {
        yield 'no config' => [null, RedisCluster::class, MasterSlaveReplication::class];
        yield 'single server as string' => [[
            'servers' => 'tcp://127.0.0.1:6379',
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
        ], RedisCluster::class, MasterSlaveReplication::class];
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
    }
}
