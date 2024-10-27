<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Doctrine\Mapping\EnhancedPHPDriver;

class ConfigurationFactory
{
    public function __invoke(ContainerInterface $container): Configuration
    {
        $globalConfig = $container->get('config');
        $isDevMode = (bool) ($globalConfig['debug'] ?? false);
        $cache = $container->get(CacheItemPoolInterface::class);
        $emConfig = $globalConfig['entity_manager'] ?? [];
        $ormConfig = $emConfig['orm'] ?? [];
        $funcStyle = $ormConfig['load_mappings_using_functional_style'] ?? false;
        $defaultRepo = $ormConfig['default_repository_classname'] ?? null;

        $this->registerTypes($ormConfig);

        $config = new Configuration();
        $config->setMetadataCache($cache);
        $config->setQueryCache($cache);
        $config->setResultCache($cache);
        $config->setProxyDir($ormConfig['proxies_dir'] ?? '');
        $config->setProxyNamespace('DoctrineProxies');
        $config->setAutoGenerateProxyClasses($isDevMode);
        $config->setMetadataDriverImpl(
            new EnhancedPHPDriver($ormConfig['entities_mappings'] ?? [], $emConfig, $funcStyle),
        );

        if ($defaultRepo !== null) {
            $config->setDefaultRepositoryClassName($defaultRepo);
        }

        return $config;
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
}
