<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine;

use Doctrine\ORM\Decorator\EntityManagerDecorator;

class ReopeningEntityManager extends EntityManagerDecorator
{
    /** @var callable */
    private $createEm;

    public function __construct(callable $createEm)
    {
        parent::__construct($createEm());
        $this->createEm = $createEm;
    }

    public function open(): void
    {
        if (! $this->wrapped->isOpen()) {
            $this->wrapped = ($this->createEm)();
        }
    }
}
