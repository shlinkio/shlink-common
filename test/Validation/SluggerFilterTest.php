<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Validation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Validation\SluggerFilter;
use Symfony\Component\String\Slugger\SluggerInterface;

use function Symfony\Component\String\u;

class SluggerFilterTest extends TestCase
{
    private SluggerFilter $filter;
    private MockObject & SluggerInterface $slugger;

    public function setUp(): void
    {
        $this->slugger = $this->createMock(SluggerInterface::class);
        $this->filter = new SluggerFilter($this->slugger);
    }

    #[Test, DataProvider('provideValuesToFilter')]
    public function providedValueIsFilteredAsExpected(string|null $providedValue, string|null $expectedValue): void
    {
        $this->slugger->expects($this->exactly($expectedValue !== null ? 1 : 0))->method('slug')->with(
            $providedValue,
        )->willReturn(u('slug'));

        $result = $this->filter->filter($providedValue);

        self::assertEquals($expectedValue, $result);
    }

    public static function provideValuesToFilter(): iterable
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', 'slug'];
        yield 'not empty string' => ['foo', 'slug'];
    }

    #[Test, DataProvider('provideValuesToFilterWithCasing')]
    public function internalSluggerKeepsCasing(string $providedValue, string $expectedValue): void
    {
        $filter = new SluggerFilter();
        self::assertEquals($expectedValue, $filter->filter($providedValue));
    }

    public static function provideValuesToFilterWithCasing(): iterable
    {
        yield ['FoO baR', 'FoO-baR'];
        yield ['  FoO/bar', 'FoO-bar'];
        yield ['  FoO/bar  ', 'FoO-bar'];
        yield ['foobar  ', 'foobar'];
        yield ['fo ob ar  ', 'fo-ob-ar'];
        yield ['/', ''];
    }
}
