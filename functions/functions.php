<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Cake\Chronos\Chronos;
use JsonSerializable;
use Shlinkio\Shlink\Common\Util\DateRange;

use function array_pad;
use function explode;
use function Shlinkio\Shlink\Json\json_decode as shlink_json_decode;
use function Shlinkio\Shlink\Json\json_encode as shlink_json_encode;

/** @deprecated Use the same function from shlinkio/shlink-json */
function json_decode(string $json): array
{
    return shlink_json_decode($json);
}

/** @deprecated Use the same function from shlinkio/shlink-json */
function json_encode(array|JsonSerializable $payload): string
{
    return shlink_json_encode($payload);
}

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
