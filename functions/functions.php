<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Cake\Chronos\Chronos;
use DateTimeInterface;
use Shlinkio\Shlink\Common\Util\DateRange;

use function array_pad;
use function date_default_timezone_get;
use function explode;

function buildDateRange(Chronos|null $startDate, Chronos|null $endDate): DateRange
{
    return match (true) {
        $startDate !== null && $endDate !== null => DateRange::between($startDate, $endDate),
        $startDate !== null => DateRange::since($startDate),
        $endDate !== null => DateRange::until($endDate),
        default => DateRange::allTime(),
    };
}

/**
 * @return array{string, string|null}
 */
function parseOrderBy(string $orderBy): array
{
    return array_pad(explode('-', $orderBy), 2, null); // @phpstan-ignore-line
}

/**
 * Parse any date-like object into a Chronos instance, or null if the input was also null.
 * Resulting date is set in the system's timezone via `date_default_timezone_get()` call.
 *
 * @return ($date is null ? null : Chronos)
 */
function normalizeOptionalDate(string|DateTimeInterface|Chronos|null $date): Chronos|null
{
    $parsedDate = match (true) {
        $date === null || $date instanceof Chronos => $date,
        $date instanceof DateTimeInterface => Chronos::instance($date),
        default => Chronos::parse($date),
    };

    return $parsedDate?->setTimezone(date_default_timezone_get());
}

/**
 * Parse any date-like object into a Chronos instance.
 * Resulting date is set in the system's timezone via `date_default_timezone_get()` call.
 */
function normalizeDate(string|DateTimeInterface|Chronos $date): Chronos
{
    return normalizeOptionalDate($date);
}
