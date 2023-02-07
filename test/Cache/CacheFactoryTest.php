<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Cache;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface as PredisClient;
use Predis\Configuration\Options;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Cache\CacheFactory;
use Symfony\Component\Cache\Adapter;
use Symfony\Component\Cache\Adapter\AdapterInterface;

use function Functional\const_function;

class CacheFactoryTest extends TestCase
{
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /**
     * @param class-string<AdapterInterface> $expectedAdapterClass
     */
    #[Test, DataProvider('provideCacheConfig')]
    public function expectedCacheAdapterIsReturned(
        array $config,
        string $expectedAdapterClass,
        callable $apcuEnabled,
    ): void {
        $factory = new CacheFactory($apcuEnabled);

        $predis = $this->createMock(PredisClient::class);
        $predis->method('getOptions')->willReturn(new Options(['exceptions' => false]));

        $this->container->expects($this->exactly($expectedAdapterClass === Adapter\RedisAdapter::class ? 2 : 1))
                        ->method('get')
                        ->willReturnMap([
                            ['config', $config],
                            [PredisClient::class, $predis],
                        ]);

        $cache = $factory($this->container);

        self::assertInstanceOf($expectedAdapterClass, $cache);
    }

    public static function provideCacheConfig(): iterable
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
