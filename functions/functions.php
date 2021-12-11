<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Cake\Chronos\Chronos;
use JsonSerializable;
use Shlinkio\Shlink\Common\Util\DateRange;

use function getenv;
use function json_decode as spl_json_decode;
use function json_encode as spl_json_encode;
use function strtolower;
use function trim;

use const JSON_THROW_ON_ERROR;

function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
    }

    return trim($value);
}

function json_decode(string $json): array
{
    return spl_json_decode($json, true, 512, JSON_THROW_ON_ERROR);
}

function json_encode(array|JsonSerializable $payload): string
{
    return spl_json_encode($payload, JSON_THROW_ON_ERROR);
}

function buildDateRange(?Chronos $startDate, ?Chronos $endDate): DateRange
{
    return match (true) {
        $startDate !== null && $endDate !== null => DateRange::withStartAndEndDate($startDate, $endDate),
        $startDate !== null => DateRange::withStartDate($startDate),
        $endDate !== null => DateRange::withEndDate($endDate),
        default => DateRange::emptyInstance(),
    };
}
