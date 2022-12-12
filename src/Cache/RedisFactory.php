<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Cache;

use Predis\Client as PredisClient;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Exception\InvalidArgumentException;

use function array_map;
use function count;
use function explode;
use function is_array;
use function is_string;
use function parse_url;
use function sprintf;
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

    private function resolveServers(array $redisConfig): array
    {
        $servers = $redisConfig['servers'] ?? [];
        $servers = array_map($this->normalizeServer(...), is_string($servers) ? explode(',', $servers) : $servers);

        // If there's only one server, we return it as is. If an array is provided, predis expects cluster config
        return count($servers) === 1 ? $servers[0] : $servers;
    }

    private function normalizeServer(string $server): array
    {
        $parsedServer = parse_url(trim($server));
        if (! is_array($parsedServer)) {
            throw new InvalidArgumentException(sprintf(
                'Provided server "%s" is not a valid URL with format schema://[[username:]password@]host:port',
                $server,
            ));
        }

        if (! isset($parsedServer['user']) && ! isset($parsedServer['pass'])) {
            return $parsedServer;
        }

        if (isset($parsedServer['user']) && ! isset($parsedServer['pass'])) {
            $parsedServer['password'] = $parsedServer['user'];
        } elseif (isset($parsedServer['user'], $parsedServer['pass'])) {
            $parsedServer['username'] = $parsedServer['user'];
            $parsedServer['password'] = $parsedServer['pass'];
        }

        unset($parsedServer['user'], $parsedServer['pass']);

        return $parsedServer;
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

        return ! isset($servers[0]) ? null : ['cluster' => 'redis'];
    }
}
