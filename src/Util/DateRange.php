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

    public function isEmpty(): bool
    {
        return $this->startDate === null && $this->endDate === null;
    }

    /** @deprecated Use DateRange::allTime() instead */
    public static function emptyInstance(): self
    {
        return self::allTime();
    }

    /** @deprecated Use DateRange::since(...) instead */
    public static function withStartDate(Chronos $startDate): self
    {
        return self::since($startDate);
    }

    /** @deprecated Use DateRange::until(...) instead */
    public static function withEndDate(Chronos $endDate): self
    {
        return self::until($endDate);
    }

    /** @deprecated Use DateRange::between(...) instead */
    public static function withStartAndEndDate(Chronos $startDate, Chronos $endDate): self
    {
        return self::between($startDate, $endDate);
    }

    /** @deprecated Use direct access to startDate property */
    public function startDate(): ?Chronos
    {
        return $this->startDate;
    }

    /** @deprecated Use direct access to endDate property */
    public function endDate(): ?Chronos
    {
        return $this->endDate;
    }
}
