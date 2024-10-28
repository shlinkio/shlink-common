<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Functions;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Shlinkio\Shlink\Common\buildDateRange;

class FunctionsTest extends TestCase
{
    #[Test, DataProvider('provideDates')]
    public function expectedDateRangeIsBuilt(
        Chronos|null $startDate,
        Chronos|null $endDate,
        bool $expectedIsAllTime,
    ): void {
        $dateRange = buildDateRange($startDate, $endDate);

        self::assertEquals($expectedIsAllTime, $dateRange->isAllTime());
        self::assertSame($startDate, $dateRange->startDate);
        self::assertSame($endDate, $dateRange->endDate);
    }

    public static function provideDates(): iterable
    {
        yield 'allTime' => [null, null, true];
        yield 'since' => [Chronos::now(), null, false];
        yield 'until' => [null, Chronos::now(), false];
        yield 'between' => [Chronos::now(), Chronos::now(), false];
    }
}
