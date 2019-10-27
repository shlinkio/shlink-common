<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;

class ReopeningEntityManager extends EntityManagerDecorator
{
    /** @var callable */
    private $createEm;

    public function __construct(callable $createEm)
    {
        parent::__construct($createEm());
        $this->createEm = $createEm;
    }

    public function flush($entity = null): void
    {
        $this->getWrappedEntityManager()->flush($entity);
    }

    public function persist($object): void
    {
        $this->getWrappedEntityManager()->persist($object);
    }

    public function remove($object): void
    {
        $this->getWrappedEntityManager()->remove($object);
    }

    public function refresh($object): void
    {
        $this->getWrappedEntityManager()->refresh($object);
    }

    public function merge($object)
    {
        return $this->getWrappedEntityManager()->merge($object);
    }

    private function getWrappedEntityManager(): EntityManagerInterface
    {
        if (! $this->wrapped->isOpen()) {
            $this->wrapped = ($this->createEm)();
        }

        return $this->wrapped;
    }
}
