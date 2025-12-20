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
use function filter_var;
use function is_array;
use function is_string;
use function parse_str;
use function parse_url;
use function sprintf;
use function trim;
use function urldecode;

use const FILTER_VALIDATE_INT;

class RedisFactory
{
    public const string SERVICE_NAME = 'Shlinkio\Shlink\Common\Cache\RedisClient';

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

        $servers = array_map(
            fn (string $server) => $this->normalizeServer($server),
            is_string($servers) ? explode(',', $servers) : $servers,
        );

        // If there's only one server, we return it as is. If an array is provided, predis expects cluster config
        return count($servers) === 1 ? $servers[0] : $servers;
    }

    private function normalizeServer(string $server): array
    {
        $parsedServer = parse_url(trim($server));
        if (! is_array($parsedServer)) {
            throw new InvalidArgumentException(sprintf(
                'Provided server "%s" is not a valid URL with format %s',
                $server,
                'schema://[[username]:password@]host:port[/database]',
            ));
        }

        // Set SSL options if schema indicates encryption should be used
        $scheme = $parsedServer['scheme'] ?? null;
        if ($scheme === 'tls' || $scheme === 'rediss') {
            $parsedServer['ssl'] = SSL::OPTIONS;
        }

        // Set credentials if set
        $user = $parsedServer['user'] ?? null;
        $pass = $parsedServer['pass'] ?? null;
        unset($parsedServer['user'], $parsedServer['pass']);

        if ($user !== null && $user !== '') {
            $parsedServer['username'] = urldecode($user);
        }
        if ($pass !== null) {
            $parsedServer['password'] = urldecode($pass);
        }

        $database = $this->resolveDatabaseIndex($parsedServer);
        if ($database !== null) {
            $parsedServer['database'] = $database;
        }

        unset($parsedServer['query']);
        if ($scheme !== 'unix') {
            // Unset both path and query for non-socket connections
            unset($parsedServer['path']);
        }

        return $parsedServer;
    }

    private function resolveDatabaseIndex(array $parsedServer): int|null
    {
        $rawQuery = $parsedServer['query'] ?? null;

        /** @var array{'database'?: string} $parsedQuery */
        $parsedQuery = [];
        if ($rawQuery !== null) {
            parse_str($rawQuery, $parsedQuery);
        }
        $rawDatabase = $parsedQuery['database'] ?? null;

        if ($rawDatabase === null) {
            return null;
        }

        $intDatabase = filter_var($rawDatabase, FILTER_VALIDATE_INT);
        if ($intDatabase === false) {
            throw new InvalidArgumentException(
                // @phpstan-ignore argument.type
                sprintf('The redis database index should be an integer, %s provided', $rawDatabase),
            );
        }

        return $intDatabase;
    }

    private function resolveOptions(array $redisConfig, array $servers): array|null
    {
        $sentinelService = $redisConfig['sentinel_service'] ?? null;
        if ($sentinelService === null) {
            return ! isset($servers[0]) ? null : ['cluster' => 'redis'];
        }

        $password = $redisConfig['password'] ?? null;
        $baseSentinelConfig = [
            'replication' => 'sentinel',
            'service' => $sentinelService,
        ];

        if ($password === null) {
            return $baseSentinelConfig;
        }

        return [
            ...$baseSentinelConfig,
            'parameters' => [
                // When using sentinel mode, since the list of servers is the list of sentinels, we need an alternative
                // way to provide credentials for the redis instances
                'username' => $redisConfig['username'] ?? null,
                'password' => $password,
            ],
        ];
    }
}
