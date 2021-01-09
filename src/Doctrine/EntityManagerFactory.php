<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Doctrine\Mapping\EnhancedPHPDriver;

class EntityManagerFactory
{
    /**
     * @throws ORMException
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container): EntityManager
    {
        $globalConfig = $container->get('config');
        $isDevMode = (bool) ($globalConfig['debug'] ?? false);
        $cache = $container->has(Cache::class) ? $container->get(Cache::class) : new ArrayCache();
        $emConfig = $globalConfig['entity_manager'] ?? [];
        $connectionConfig = $emConfig['connection'] ?? [];
        $ormConfig = $emConfig['orm'] ?? [];
        $funcStyle = $ormConfig['load_mappings_using_functional_style'] ?? false;
        $defaultRepo = $ormConfig['default_repository_classname'] ?? null;

        $this->registerTypes($ormConfig);

        $config = Setup::createConfiguration($isDevMode, $ormConfig['proxies_dir'] ?? null, $cache);
        $config->setMetadataDriverImpl(
            new EnhancedPHPDriver($ormConfig['entities_mappings'] ?? [], $emConfig, $funcStyle),
        );

        if ($defaultRepo !== null) {
            $config->setDefaultRepositoryClassName($defaultRepo);
        }

        return EntityManager::create($connectionConfig, $config);
    }

    /**
     * @throws Exception
     */
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
