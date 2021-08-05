<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Cache;

use Doctrine\Common\Cache;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;

class DoctrineCacheFactory
{
    public function __invoke(ContainerInterface $container): Cache\Cache
    {
        $psrCache = $container->get(CacheItemPoolInterface::class);
        return Cache\Psr6\DoctrineProvider::wrap($psrCache);
    }
}
