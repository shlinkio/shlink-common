<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Validation;

use Cocur\Slugify\SlugifyInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\Common\Validation\SluggerFilter;

class SluggerFilterTest extends TestCase
{
    private SluggerFilter $filter;
    private ObjectProphecy $slugger;

    public function setUp(): void
    {
        $this->slugger = $this->prophesize(SlugifyInterface::class);
        $this->filter = new SluggerFilter($this->slugger->reveal());
    }

    /**
     * @test
     * @dataProvider provideValuesToFilter
     */
    public function providedValueIsFilteredAsExpected(?string $providedValue, ?string $expectedValue): void
    {
        $slugify = $this->slugger->slugify($providedValue)->willReturn('slug');

        $result = $this->filter->filter($providedValue);

        $this->assertEquals($expectedValue, $result);
        $slugify->shouldHaveBeenCalledTimes($expectedValue !== null ? 1 : 0);
    }

    public function provideValuesToFilter(): iterable
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', 'slug'];
        yield 'not empty string' => ['foo', 'slug'];
    }

    /** @test */
    public function internalSluggerKeepsCasing(): void
    {
        $filter = new SluggerFilter();
        $this->assertEquals('FoO-baR', $filter->filter('FoO baR'));
    }
}
