<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine;

use Closure;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Override;

/**
 * @final
 */
class ReopeningEntityManager extends EntityManagerDecorator implements ReopeningEntityManagerInterface
{
    private Closure $createEm;

    public function __construct(callable $createEm)
    {
        parent::__construct($createEm());
        $this->createEm = Closure::fromCallable($createEm);
    }

    #[Override]
    public function open(): void
    {
        if (!$this->wrapped->isOpen()) {
            $this->wrapped = ($this->createEm)();
        }
    }
}
