<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Cake\Chronos\Chronos;
use Shlinkio\Shlink\Common\Util\DateRange;

use function array_pad;
use function explode;

function buildDateRange(?Chronos $startDate, ?Chronos $endDate): DateRange
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
