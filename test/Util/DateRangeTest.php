<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Util;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Util\DateRange;

class DateRangeTest extends TestCase
{
    /** @test */
    public function defaultConstructorSetDatesToNull(): void
    {
        $range = DateRange::allTime();

        self::assertNull($range->startDate);
        self::assertNull($range->endDate);
        self::assertTrue($range->isAllTime());
    }

    /** @test */
    public function providedDatesAreSet(): void
    {
        $startDate = Chronos::now()->subDays(3);
        $endDate = Chronos::now();
        $range = DateRange::between($startDate, $endDate);

        self::assertSame($startDate, $range->startDate);
        self::assertSame($endDate, $range->endDate);
        self::assertFalse($range->isAllTime());
    }

    /** @test */
    public function isCreatedWithStartDate(): void
    {
        $startDate = Chronos::now();
        $range = DateRange::since($startDate);

        self::assertFalse($range->isAllTime());
        self::assertNull($range->endDate);
        self::assertSame($startDate, $range->startDate);
    }

    /** @test */
    public function isCreatedWithEndDate(): void
    {
        $endDate = Chronos::now();
        $range = DateRange::until($endDate);

        self::assertFalse($range->isAllTime());
        self::assertNull($range->startDate);
        self::assertSame($endDate, $range->endDate);
    }
}
