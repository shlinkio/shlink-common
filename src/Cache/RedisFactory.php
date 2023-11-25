<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Cache;

use Predis\Client as PredisClient;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Exception\InvalidArgumentException;
use Shlinkio\Shlink\Common\Util\SSL;

use function array_map;
use function count;
use function explode;
use function Functional\id;
use function is_array;
use function is_string;
use function parse_url;
use function sprintf;
use function trim;
use function urldecode;

class RedisFactory
{
    public const SERVICE_NAME = 'Shlinkio\Shlink\Common\Cache\RedisClient';

    public function __invoke(ContainerInterface $container): PredisClient
    {
        $redisConfig = $container->get('config')['cache']['redis'] ?? [];
        $servers = $this->resolveServers($redisConfig);
        $options = $this->resolveOptions($redisConfig, $servers);

        return new ShlinkPredisClient($servers, $options);
    }

    private function resolveServers(array $redisConfig): array
    {
        $servers = $redisConfig['servers'] ?? [];
        $decodeCredentials = $redisConfig['decode_credentials'] ?? false;

        $servers = array_map(
            fn (string $server) => $this->normalizeServer($server, $decodeCredentials),
            is_string($servers) ? explode(',', $servers) : $servers,
        );

        // If there's only one server, we return it as is. If an array is provided, predis expects cluster config
        return count($servers) === 1 ? $servers[0] : $servers;
    }

    private function normalizeServer(string $server, bool $decodeCredentials): array
    {
        $parsedServer = parse_url(trim($server));
        if (! is_array($parsedServer)) {
            throw new InvalidArgumentException(sprintf(
                'Provided server "%s" is not a valid URL with format schema://[[username]:password@]host:port',
                $server,
            ));
        }

        // Set SSL options if schema indicates encryption should be used
        $scheme = $parsedServer['scheme'] ?? null;
        if ($scheme === 'tls' || $scheme === 'rediss') {
            $parsedServer['ssl'] = SSL::OPTIONS;
        }

        if (! isset($parsedServer['user']) && ! isset($parsedServer['pass'])) {
            return $parsedServer;
        }

        // Apply URL decoding only if explicitly requested, for BC. Next major version will always do it
        $credentialsCallback = $decodeCredentials ? urldecode(...) : id(...);

        if (isset($parsedServer['user']) && ! isset($parsedServer['pass'])) {
            // For historical reasons, we support URLs in the form of `tcp://redis_password@redis_host:1234`, but this
            // is deprecated
            $parsedServer['password'] = $credentialsCallback($parsedServer['user']);
        } elseif (isset($parsedServer['user'], $parsedServer['pass'])) {
            if ($parsedServer['user'] !== '') {
                $parsedServer['username'] = $credentialsCallback($parsedServer['user']);
            }
            $parsedServer['password'] = $credentialsCallback($parsedServer['pass']);
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
