<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\ObjectMapper;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\ObjectMapper\TagsConverter;

class TagsConverterTest extends TestCase
{
    private TagsConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new TagsConverter();
    }

    #[Test]
    #[TestWith([['foo', 'bar', 'baz'], ['foo', 'bar', 'baz']])]
    #[TestWith([['fo o', '  bar', 'b az'], ['fo-o', 'bar', 'b-az']])]
    #[TestWith([['UUU', 'Aäa'], ['uuu', 'aäa']])]
    #[TestWith([['<script></script> the tag'], ['the-tag']])]
    public function mapsTagsAsExpected(array $tags, array $expected): void
    {
        self::assertEquals($expected, $this->converter->map($tags));
    }
}
