<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Predis\Client as PredisClient;
use Predis\ClientInterface as PredisClientInterface;
use Psr\Cache\CacheItemPoolInterface as PsrCache;
use Psr\SimpleCache\CacheInterface as PsrSimpleCache;
use Symfony\Component\Cache\Psr16Cache;

return [

    'dependencies' => [
        'factories' => [
            PsrCache::class => Cache\CacheFactory::class,
            Psr16Cache::class => ConfigAbstractFactory::class,
            Cache\RedisFactory::SERVICE_NAME => Cache\RedisFactory::class,
            Cache\RedisPublishingHelper::class => ConfigAbstractFactory::class,
        ],
        'aliases' => [
            PredisClient::class => Cache\RedisFactory::SERVICE_NAME,
            PredisClientInterface::class => Cache\RedisFactory::SERVICE_NAME,
            PsrSimpleCache::class => Psr16Cache::class,
        ],
    ],

    ConfigAbstractFactory::class => [
        Cache\RedisPublishingHelper::class => [Cache\RedisFactory::SERVICE_NAME],
        Psr16Cache::class => [PsrCache::class],
    ],

];
