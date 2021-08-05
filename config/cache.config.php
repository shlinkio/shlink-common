<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Doctrine\Common\Cache\Cache as DoctrineCache;
use Predis\Client as PredisClient;
use Predis\ClientInterface as PredisClientInterface;
use Psr\Cache\CacheItemPoolInterface as PsrCache;

return [

    'dependencies' => [
        'factories' => [
            PsrCache::class => Cache\CacheFactory::class,
            DoctrineCache::class => Cache\DoctrineCacheFactory::class,
            PredisClientInterface::class => Cache\RedisFactory::class,
        ],
        'aliases' => [
            PredisClient::class => PredisClientInterface::class,
        ],
    ],

];
