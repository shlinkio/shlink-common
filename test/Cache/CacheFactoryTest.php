<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Cache;

use PHPUnit\Framework\TestCase;
use Predis\ClientInterface as PredisClient;
use Predis\Configuration\Options;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Cache\CacheFactory;
use Symfony\Component\Cache\Adapter;

use function Functional\const_function;

class CacheFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @test
     * @dataProvider provideCacheConfig
     */
    public function expectedCacheAdapterIsReturned(
        array $config,
        string $expectedAdapterClass,
        callable $apcuEnabled,
    ): void {
        $factory = new CacheFactory($apcuEnabled);

        $predis = $this->prophesize(PredisClient::class);
        $predis->getOptions()->willReturn(new Options(['exceptions' => false]));

        $getConfig = $this->container->get('config')->willReturn($config);
        $getRedis = $this->container->get(PredisClient::class)->willReturn($predis->reveal());

        $cache = $factory($this->container->reveal());

        self::assertInstanceOf($expectedAdapterClass, $cache);
        $getConfig->shouldHaveBeenCalledOnce();
        $getRedis->shouldHaveBeenCalledTimes($expectedAdapterClass === Adapter\RedisAdapter::class ? 1 : 0);
    }

    public function provideCacheConfig(): iterable
    {
        $withApcu = const_function(true);
        $withoutApcu = const_function(false);

        yield 'debug true and apcu enabled' => [['debug' => true], Adapter\ArrayAdapter::class, $withApcu];
        yield 'debug true and apcu disabled' => [['debug' => true], Adapter\ArrayAdapter::class, $withoutApcu];
        yield 'debug false and apcu enabled' => [['debug' => false], Adapter\ApcuAdapter::class, $withApcu];
        yield 'debug false and apcu disabled' => [['debug' => false], Adapter\ArrayAdapter::class, $withoutApcu];
        yield 'no debug and apcu enabled' => [[], Adapter\ApcuAdapter::class, $withApcu];
        yield 'no debug and apcu disabled' => [[], Adapter\ArrayAdapter::class, $withoutApcu];
        yield 'redis configured' => [['cache' => [
            'namespace' => 'some_namespace',
            'redis' => [],
        ]], Adapter\RedisAdapter::class, $withApcu];
    }
}
