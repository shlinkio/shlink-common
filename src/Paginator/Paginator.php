<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Paginator;

use Pagerfanta\Pagerfanta;

use function max;

class Paginator extends Pagerfanta
{
    public const ALL_ITEMS = -1;

    private bool $returnAllItems = false;

    /**
     * @param positive-int|self::ALL_ITEMS $maxPerPage
     */
    public function setMaxPerPage(int $maxPerPage): self
    {
        $this->returnAllItems = $maxPerPage < 1;
        if ($maxPerPage >= 1) {
            parent::setMaxPerPage($maxPerPage);
        }

        return $this;
    }

    public function getMaxPerPage(): int
    {
        if (! $this->returnAllItems) {
            return parent::getMaxPerPage();
        }

        return max(parent::getNbResults(), 1);
    }
}
