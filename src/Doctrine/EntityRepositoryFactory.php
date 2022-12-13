<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

use function is_subclass_of;

class EntityRepositoryFactory
{
    public static function __callStatic(string $entityName, array $arguments): mixed
    {
        /** @var ContainerInterface $container */
        /** @var string $repositoryClass */
        [$container, $repositoryClass] = $arguments;
        $em = $container->get(EntityManager::class);

        $baseRepo = $em->getConfiguration()->getDefaultRepositoryClassName();
        if ($repositoryClass !== $baseRepo && ! is_subclass_of($repositoryClass, $baseRepo)) {
            // TODO Throw exception
        }

        $classMeta = $em->getClassMetadata($entityName);
        return new $repositoryClass($em, $classMeta);
    }
}
