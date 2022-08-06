<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Util;

use Cake\Chronos\Chronos;

final class DateRange
{
    private function __construct(public readonly ?Chronos $startDate = null, public readonly ?Chronos $endDate = null)
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
