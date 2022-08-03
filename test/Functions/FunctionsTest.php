<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Functions;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;

use function Shlinkio\Shlink\Common\buildDateRange;

class FunctionsTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideDates
     */
    public function expectedDateRangeIsBuilt(?Chronos $startDate, ?Chronos $endDate, bool $expectedIsAllTime): void
    {
        $dateRange = buildDateRange($startDate, $endDate);

        self::assertEquals($expectedIsAllTime, $dateRange->isAllTime());
        self::assertSame($startDate, $dateRange->startDate);
        self::assertSame($endDate, $dateRange->endDate);
    }

    public function provideDates(): iterable
    {
        yield 'allTime' => [null, null, true];
        yield 'since' => [Chronos::now(), null, false];
        yield 'until' => [null, Chronos::now(), false];
        yield 'between' => [Chronos::now(), Chronos::now(), false];
    }
}
