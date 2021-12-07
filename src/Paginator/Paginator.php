<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Paginator;

use Pagerfanta\Pagerfanta;

class Paginator extends Pagerfanta
{
    private bool $allResults = false;

    public function setMaxPerPage(int $maxPerPage): self
    {
        $this->allResults = $maxPerPage < 1; // @phpstan-ignore-line

        if (! $this->allResults) { // @phpstan-ignore-line
            parent::setMaxPerPage($maxPerPage);
        }

        return $this;
    }

    public function getMaxPerPage(): int
    {
        if (! $this->allResults) {
            return parent::getMaxPerPage();
        }

        $numberOfResults = parent::getNbResults();
        return $numberOfResults === null || $numberOfResults < 1 ? 1 : $numberOfResults; // @phpstan-ignore-line
    }
}
