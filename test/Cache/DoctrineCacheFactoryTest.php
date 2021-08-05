<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Cache;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Cache\DoctrineCacheFactory;

class DoctrineCacheFactoryTest extends TestCase
{
    use ProphecyTrait;

    private DoctrineCacheFactory $factory;
    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->factory = new DoctrineCacheFactory();
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /** @test */
    public function wrapsPsrInstance(): void
    {
        $getCache = $this->container->get(CacheItemPoolInterface::class)->willReturn(
            $this->prophesize(CacheItemPoolInterface::class)->reveal(),
        );

        ($this->factory)($this->container->reveal());

        $getCache->shouldHaveBeenCalledOnce();
    }
}
