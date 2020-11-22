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
        $range = new DateRange();
        self::assertNull($range->getStartDate());
        self::assertNull($range->getEndDate());
        self::assertTrue($range->isEmpty());
    }

    /** @test */
    public function providedDatesAreSet(): void
    {
        $startDate = Chronos::now();
        $endDate = Chronos::now();
        $range = new DateRange($startDate, $endDate);
        self::assertSame($startDate, $range->getStartDate());
        self::assertSame($endDate, $range->getEndDate());
        self::assertFalse($range->isEmpty());
    }

    /**
     * @test
     * @dataProvider provideDates
     */
    public function isConsideredEmptyOnlyIfNoneOfTheDatesIsSet(
        ?Chronos $startDate,
        ?Chronos $endDate,
        bool $isEmpty
    ): void {
        $range = new DateRange($startDate, $endDate);
        self::assertEquals($isEmpty, $range->isEmpty());
    }

    public function provideDates(): iterable
    {
        yield 'both are null' => [null, null, true];
        yield 'start is null' => [null, Chronos::now(), false];
        yield 'end is null' => [Chronos::now(), null, false];
        yield 'none are null' => [Chronos::now(), Chronos::now(), false];
    }
}
