<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Util;

use Cake\Chronos\Chronos;

final class DateRange
{
    private function __construct(private ?Chronos $startDate = null, private ?Chronos $endDate = null)
    {
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

    public function startDate(): ?Chronos
    {
        return $this->startDate;
    }

    public function endDate(): ?Chronos
    {
        return $this->endDate;
    }

    public function isEmpty(): bool
    {
        return $this->startDate === null && $this->endDate === null;
    }
}
