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
        $range = DateRange::emptyInstance();

        self::assertNull($range->startDate());
        self::assertNull($range->endDate());
        self::assertTrue($range->isEmpty());
    }

    /** @test */
    public function providedDatesAreSet(): void
    {
        $startDate = Chronos::now();
        $endDate = Chronos::now();
        $range = DateRange::withStartAndEndDate($startDate, $endDate);

        self::assertSame($startDate, $range->startDate());
        self::assertSame($endDate, $range->endDate());
        self::assertFalse($range->isEmpty());
    }

    /** @test */
    public function isCreatedWithStartDate(): void
    {
        $startDate = Chronos::now();
        $range = DateRange::withStartDate($startDate);

        self::assertFalse($range->isEmpty());
        self::assertNull($range->endDate());
        self::assertSame($startDate, $range->startDate());
    }

    /** @test */
    public function isCreatedWithEndDate(): void
    {
        $endDate = Chronos::now();
        $range = DateRange::withEndDate($endDate);

        self::assertFalse($range->isEmpty());
        self::assertNull($range->startDate());
        self::assertSame($endDate, $range->endDate());
    }

    /** @test */
    public function isCreatedWithBothDates(): void
    {
        $startDate = Chronos::now();
        $endDate = Chronos::now();
        $range = DateRange::withStartAndEndDate($startDate, $endDate);

        self::assertFalse($range->isEmpty());
        self::assertSame($startDate, $range->startDate());
        self::assertSame($endDate, $range->endDate());
    }
}
