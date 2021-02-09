<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Util;

use Cake\Chronos\Chronos;

final class DateRange
{
    private ?Chronos $startDate;
    private ?Chronos $endDate;

    /** @deprecated Use named constructors */
    public function __construct(?Chronos $startDate = null, ?Chronos $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public static function emptyInstance(): self
    {
        return new self();
    }

    public static function withStartDate(Chronos $startDate): self
    {
        return new self($startDate);
    }

    public static function withEndDate(Chronos $endDate): self
    {
        return new self(null, $endDate);
    }

    public static function withStartAndEndDate(Chronos $startDate, Chronos $endDate): self
    {
        return new self($startDate, $endDate);
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
