<?php

declare(strict_types=1);

namespace ObjectMapper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\ObjectMapper\SubstringConverter;

use function str_pad;

use const STR_PAD_BOTH;

class SubstringConverterTest extends TestCase
{
    private SubstringConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new SubstringConverter(512);
    }

    #[Test, DataProvider('provideTitles')]
    public function mapsValueAsExpected(string $value, string $expected): void
    {
        self::assertEquals($expected, $this->converter->map($value));
    }

    public static function provideTitles(): iterable
    {
        yield ['foo', 'foo'];
        yield [str_pad('bar', 600, ' ', STR_PAD_BOTH), 'bar'];
        yield [str_pad('', 511, 'a'), str_pad('', 511, 'a')];
        yield [str_pad('', 512, 'b'), str_pad('', 512, 'b')];
        yield [str_pad('', 513, 'c'), str_pad('', 512, 'c')];
        yield [str_pad('', 600, 'd'), str_pad('', 512, 'd')];
        yield [str_pad('', 800, 'e'), str_pad('', 512, 'e')];
    }
}
