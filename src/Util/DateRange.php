<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Util;

use Cake\Chronos\Chronos;

final class DateRange
{
    private ?Chronos $startDate;
    private ?Chronos $endDate;

    public function __construct(?Chronos $startDate = null, ?Chronos $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate(): ?Chronos
    {
        return $this->startDate;
    }

    public function getEndDate(): ?Chronos
    {
        return $this->endDate;
    }

    public function isEmpty(): bool
    {
        return $this->startDate === null && $this->endDate === null;
    }
}
