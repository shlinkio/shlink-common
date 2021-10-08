<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Cache;

use Predis\Client as PredisClient;
use Psr\Container\ContainerInterface;

use function count;
use function explode;
use function is_string;

class RedisFactory
{
    public function __invoke(ContainerInterface $container): PredisClient
    {
        $config = $container->get('config');
        $redisConfig = $config['cache']['redis'] ?? [];

        $servers = $redisConfig['servers'] ?? [];
        $servers = is_string($servers) ? explode(',', $servers) : $servers;
        $options = $this->resolveOptions($redisConfig, $servers);

        return new PredisClient($servers, $options);
    }

    private function resolveOptions(array $redisConfig, array $servers): ?array
    {
        $sentinelService = $redisConfig['sentinel_service'] ?? null;
        if ($sentinelService !== null) {
            return [
                'replication' => 'sentinel',
                'service' => $sentinelService,
            ];
        }

        return count($servers) <= 1 ? null : ['cluster' => 'redis'];
    }
}
