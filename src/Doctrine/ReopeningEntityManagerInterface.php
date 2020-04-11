<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

interface ReopeningEntityManagerInterface extends EntityManagerInterface
{
    public function open(): void;
}
