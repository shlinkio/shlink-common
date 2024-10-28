<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Cache;

use Closure;
use Predis\ClientInterface as PredisClient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter;

use function extension_loaded;

class CacheFactory
{
    private Closure $apcuEnabled;

    public function __construct(callable|null $apcuEnabled = null)
    {
        $this->apcuEnabled = Closure::fromCallable($apcuEnabled ?? static fn () => extension_loaded('apcu'));
    }

    public function __invoke(ContainerInterface $container): CacheItemPoolInterface
    {
        $apcuEnabled = ($this->apcuEnabled)();
        $config = $container->get('config');
        $isDebug = (bool) ($config['debug'] ?? false);
        $redisConfig = $config['cache']['redis'] ?? null;
        $lifetime = (int) ($config['cache']['default_lifetime'] ?? 0);

        if ($isDebug || (! $apcuEnabled && $redisConfig === null)) {
            return new Adapter\ArrayAdapter($lifetime);
        }

        $namespace = $config['cache']['namespace'] ?? '';
        if ($redisConfig === null) {
            return new Adapter\ApcuAdapter($namespace, $lifetime);
        }

        $predis = $container->get(PredisClient::class);
        return new Adapter\RedisAdapter($predis, $namespace, $lifetime);
    }
}
