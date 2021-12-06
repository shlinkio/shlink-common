<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Cake\Chronos\Chronos;
use Shlinkio\Shlink\Common\Util\DateRange;

use function getenv;
use function json_decode as spl_json_decode;
use function json_last_error;
use function json_last_error_msg;
use function sprintf;
use function strtolower;
use function trim;

use const JSON_ERROR_NONE;

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

/**
 * @throws Exception\InvalidArgumentException
 * @param int<1, max> $depth
 */
function json_decode(string $json, int $depth = 512, int $options = 0): array
{
    $data = spl_json_decode($json, true, $depth, $options);
    if (JSON_ERROR_NONE !== json_last_error()) {
        throw new Exception\InvalidArgumentException(sprintf('Error decoding JSON: %s', json_last_error_msg()));
    }

    return $data;
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
