<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Validation;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\Common\Validation\SluggerFilter;
use Symfony\Component\String\Slugger\SluggerInterface;

use function Symfony\Component\String\u;

class SluggerFilterTest extends TestCase
{
    use ProphecyTrait;

    private SluggerFilter $filter;
    private ObjectProphecy $slugger;

    public function setUp(): void
    {
        $this->slugger = $this->prophesize(SluggerInterface::class);
        $this->filter = new SluggerFilter($this->slugger->reveal());
    }

    /**
     * @test
     * @dataProvider provideValuesToFilter
     */
    public function providedValueIsFilteredAsExpected(?string $providedValue, ?string $expectedValue): void
    {
        $slugify = $this->slugger->slug($providedValue)->willReturn(u('slug'));

        $result = $this->filter->filter($providedValue);

        self::assertEquals($expectedValue, $result);
        $slugify->shouldHaveBeenCalledTimes($expectedValue !== null ? 1 : 0);
    }

    public function provideValuesToFilter(): iterable
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', 'slug'];
        yield 'not empty string' => ['foo', 'slug'];
    }

    /**
     * @test
     * @dataProvider provideValuesToFilterWithCasing
     */
    public function internalSluggerKeepsCasing(string $providedValue, string $expectedValue): void
    {
        $filter = new SluggerFilter();
        self::assertEquals($expectedValue, $filter->filter($providedValue));
    }

    public function provideValuesToFilterWithCasing(): iterable
    {
        yield ['FoO baR', 'FoO-baR'];
        yield ['  FoO/bar', 'FoO-bar'];
        yield ['  FoO/bar  ', 'FoO-bar'];
        yield ['foobar  ', 'foobar'];
        yield ['fo ob ar  ', 'fo-ob-ar'];
        yield ['/', ''];
    }
}
