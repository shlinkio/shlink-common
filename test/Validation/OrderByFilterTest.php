<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Validation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Validation\OrderByFilter;
use stdClass;

class OrderByFilterTest extends TestCase
{
    private OrderByFilter $filter;

    public function setUp(): void
    {
        $this->filter = new OrderByFilter();
    }

    #[Test, DataProvider('provideValuesToFilter')]
    public function filterReturnsExpectedValue(mixed $value, array $expectedResult): void
    {
        self::assertEquals($expectedResult, $this->filter->filter($value));
    }

    public static function provideValuesToFilter(): iterable
    {
        $defaultValue = [null, null];

        yield [null, $defaultValue];
        yield [1, $defaultValue];
        yield [[], $defaultValue];
        yield [new stdClass(), $defaultValue];
        yield ['', ['', null]];
        yield ['field', ['field', null]];
        yield ['field-ASC', ['field', 'ASC']];
    }
}
