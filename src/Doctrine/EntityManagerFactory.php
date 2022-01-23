<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Doctrine\Mapping\EnhancedPHPDriver;

class EntityManagerFactory
{
    public function __invoke(ContainerInterface $container): EntityManager
    {
        $globalConfig = $container->get('config');
        $isDevMode = (bool) ($globalConfig['debug'] ?? false);
        $cache = $container->get(CacheItemPoolInterface::class);
        $emConfig = $globalConfig['entity_manager'] ?? [];
        $connectionConfig = $emConfig['connection'] ?? [];
        $ormConfig = $emConfig['orm'] ?? [];
        $funcStyle = $ormConfig['load_mappings_using_functional_style'] ?? false;
        $defaultRepo = $ormConfig['default_repository_classname'] ?? null;

        $this->registerTypes($ormConfig);

        $config = $this->createConfiguration($isDevMode, $ormConfig['proxies_dir'] ?? '', $cache);
        $config->setMetadataDriverImpl(
            new EnhancedPHPDriver($ormConfig['entities_mappings'] ?? [], $emConfig, $funcStyle),
        );

        if ($defaultRepo !== null) {
            $config->setDefaultRepositoryClassName($defaultRepo);
        }

        $em = EntityManager::create($connectionConfig, $config);

        $this->registerListeners($ormConfig, $em, $container);

        return $em;
    }

    private function registerTypes(array $ormConfig): void
    {
        $types = $ormConfig['types'] ?? [];

        foreach ($types as $name => $className) {
            if (! Type::hasType($name)) {
                Type::addType($name, $className);
            }
        }
    }

    private function createConfiguration(bool $isDev, string $proxyDir, CacheItemPoolInterface $cache): Configuration
    {
        $config = new Configuration();

        $config->setMetadataCache($cache);
        $config->setQueryCache($cache);
        $config->setResultCache($cache); // TODO Remove this??
        $config->setProxyDir($proxyDir);
        $config->setProxyNamespace('DoctrineProxies');
        $config->setAutoGenerateProxyClasses($isDev);

        return $config;
    }

    private function registerListeners(array $ormConfig, EntityManager $em, ContainerInterface $container): void
    {
        $listeners = $ormConfig['listeners'] ?? [];
        $events = $em->getEventManager();

        foreach ($listeners as $event => $services) {
            foreach ($services as $service) {
                $events->addEventListener($event, $container->get($service));
            }
        }
    }
}
