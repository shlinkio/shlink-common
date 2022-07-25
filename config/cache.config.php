<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Predis\Client as PredisClient;
use Predis\ClientInterface as PredisClientInterface;
use Psr\Cache\CacheItemPoolInterface as PsrCache;

return [

    'dependencies' => [
        'factories' => [
            PsrCache::class => Cache\CacheFactory::class,
            Cache\RedisFactory::SERVICE_NAME => Cache\RedisFactory::class,
            Cache\RedisPublishingHelper::class => ConfigAbstractFactory::class,
        ],
        'aliases' => [
            PredisClient::class => Cache\RedisFactory::SERVICE_NAME,
            PredisClientInterface::class => Cache\RedisFactory::SERVICE_NAME,
        ],
    ],

    ConfigAbstractFactory::class => [
        Cache\RedisPublishingHelper::class => [Cache\RedisFactory::SERVICE_NAME],
    ],

];
