<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Exception\InvalidArgumentException;

use function is_subclass_of;
use function sprintf;

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
            throw new InvalidArgumentException(sprintf(
                '"%s" cannot create an instance of "%s", as it is neither an instance of or extends from "%s"',
                __CLASS__,
                $repositoryClass,
                $baseRepo,
            ));
        }

        $classMeta = $em->getClassMetadata($entityName);
        return new $repositoryClass($em, $classMeta);
    }
}
