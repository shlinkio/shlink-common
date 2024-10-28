<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Util;

use Cake\Chronos\Chronos;

final readonly class DateRange
{
    private function __construct(public Chronos|null $startDate = null, public Chronos|null $endDate = null)
    {
    }

    public static function since(Chronos $startDate): self
    {
        return new self($startDate);
    }

    public static function until(Chronos $endDate): self
    {
        return new self(null, $endDate);
    }

    public static function between(Chronos $startDate, Chronos $endDate): self
    {
        return new self($startDate, $endDate);
    }

    public static function allTime(): self
    {
        return new self();
    }

    public function isAllTime(): bool
    {
        return $this->startDate === null && $this->endDate === null;
    }
}
