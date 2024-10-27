<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class EntityManagerFactory
{
    public function __invoke(ContainerInterface $container): EntityManager
    {
        $globalConfig = $container->get('config');
        $ormConfig = $container->get(Configuration::class);

        $emConfig = $globalConfig['entity_manager'] ?? [];
        $connectionConfig = $emConfig['connection'] ?? [];
        $em = new EntityManager(DriverManager::getConnection($connectionConfig, $ormConfig), $ormConfig);

        $this->registerListeners($emConfig['orm']['listeners'] ?? [], $em, $container);

        return $em;
    }

    private function registerListeners(array $listeners, EntityManager $em, ContainerInterface $container): void
    {
        $events = $em->getEventManager();
        foreach ($listeners as $event => $services) {
            foreach ($services as $service) {
                $events->addEventListener($event, $container->get($service));
            }
        }
    }
}
