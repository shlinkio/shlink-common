<?php

declare(strict_types=1);

namespace ObjectMapper;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\ObjectMapper\LooseUriConverter;
use Shlinkio\Shlink\Common\ObjectMapper\MappingError;

class LooseUriConverterTest extends TestCase
{
    private LooseUriConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new LooseUriConverter();
    }

    #[Test]
    #[TestWith(['invalid'])]
    #[TestWith(['not an uri'])]
    public function throwsWhenInvalidValueIsProvided(string $value): void
    {
        $this->expectException(MappingError::class);
        $this->expectExceptionMessage('Provided value does not match a loose URI pattern');

        $this->converter->map($value);
    }

    #[Test]
    #[TestWith(['https://example.com'])]
    #[TestWith(['ftp:///the/file'])]
    #[TestWith(['shlink://go-somewhere'])]
    public function mapsValueVerbatimWhenValid(string $value): void
    {
        self::assertEquals($value, $this->converter->map($value));
    }
}
