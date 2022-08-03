<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Util;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Util\DateRange;

class DateRangeTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideAllTimeMethods
     */
    public function defaultConstructorSetDatesToNull(string $method): void
    {
        $range = DateRange::{$method}();

        self::assertNull($range->startDate);
        self::assertNull($range->endDate);
        self::assertNull($range->startDate()); // Deprecated
        self::assertNull($range->endDate()); // Deprecated
        self::assertTrue($range->isEmpty());
    }

    public function provideAllTimeMethods(): iterable
    {
        yield 'emptyInstance' => ['emptyInstance']; // Deprecated
        yield 'allTime' => ['allTime'];
    }

    /**
     * @test
     * @dataProvider provideBetweenMethods
     */
    public function providedDatesAreSet(string $method): void
    {
        $startDate = Chronos::now()->subDays(3);
        $endDate = Chronos::now();
        $range = DateRange::{$method}($startDate, $endDate);

        self::assertSame($startDate, $range->startDate);
        self::assertSame($endDate, $range->endDate);
        self::assertSame($startDate, $range->startDate()); // Deprecated
        self::assertSame($endDate, $range->endDate()); // Deprecated
        self::assertFalse($range->isEmpty());
    }

    public function provideBetweenMethods(): iterable
    {
        yield 'withStartAndEndDate' => ['withStartAndEndDate']; // Deprecated
        yield 'between' => ['between'];
    }

    /**
     * @test
     * @dataProvider provideSinceMethods
     */
    public function isCreatedWithStartDate(string $method): void
    {
        $startDate = Chronos::now();
        $range = DateRange::{$method}($startDate);

        self::assertFalse($range->isEmpty());
        self::assertNull($range->endDate);
        self::assertSame($startDate, $range->startDate);
        self::assertNull($range->endDate()); // Deprecated
        self::assertSame($startDate, $range->startDate()); // Deprecated
    }

    public function provideSinceMethods(): iterable
    {
        yield 'withStartDate' => ['withStartDate']; // Deprecated
        yield 'since' => ['since'];
    }

    /**
     * @test
     * @dataProvider provideUntilMethods
     */
    public function isCreatedWithEndDate(string $method): void
    {
        $endDate = Chronos::now();
        $range = DateRange::{$method}($endDate);

        self::assertFalse($range->isEmpty());
        self::assertNull($range->startDate);
        self::assertSame($endDate, $range->endDate);
        self::assertNull($range->startDate()); // Deprecated
        self::assertSame($endDate, $range->endDate()); // Deprecated
    }

    public function provideUntilMethods(): iterable
    {
        yield 'withEndDate' => ['withEndDate']; // Deprecated
        yield 'until' => ['until'];
    }
}
