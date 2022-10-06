<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Cache;

use Predis\Client as PredisClient;
use Psr\Container\ContainerInterface;

use function array_map;
use function count;
use function explode;
use function is_string;
use function trim;

class RedisFactory
{
    public const SERVICE_NAME = 'Shlinkio\Shlink\Common\Cache\RedisClient';

    public function __invoke(ContainerInterface $container): PredisClient
    {
        $redisConfig = $container->get('config')['cache']['redis'] ?? [];
        $servers = $this->resolveServers($redisConfig);
        $options = $this->resolveOptions($redisConfig, $servers);

        return new PredisClient($servers, $options);
    }

    /**
     * @return string|string[]
     */
    private function resolveServers(array $redisConfig): string|array
    {
        $servers = $redisConfig['servers'] ?? [];
        $servers = array_map(trim(...), is_string($servers) ? explode(',', $servers) : $servers);

        // If there's only one server, Predis expects a string. If an array is provided, it also expects cluster config
        return count($servers) === 1 ? $servers[0] : $servers;
    }

    private function resolveOptions(array $redisConfig, array|string $servers): ?array
    {
        $sentinelService = $redisConfig['sentinel_service'] ?? null;
        if ($sentinelService !== null) {
            return [
                'replication' => 'sentinel',
                'service' => $sentinelService,
            ];
        }

        return is_string($servers) ? null : ['cluster' => 'redis'];
    }
}
