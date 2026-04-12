<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\ObjectMapper;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\ObjectMapper\HostAndPortConverter;
use Shlinkio\Shlink\Common\ObjectMapper\MappingError;

class HostAndPortConverterTest extends TestCase
{
    private HostAndPortConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new HostAndPortConverter();
    }

    #[Test]
    #[TestWith(['foo:bar:baz', 'Provided value, once split using the ":" separator, returned more than 2 parts'])]
    #[TestWith(['not a host name', 'The host part of the value is not valid. It must be a hostname or IP address'])]
    #[TestWith([
        '192.168.1.1:80000',
        'The port part of the value is not valid. It must be a number between 1 and 65535',
    ])]
    #[TestWith(['192.168.1.1:foo', 'The port part of the value is not valid. It must be a number between 1 and 65535'])]
    #[TestWith(['192.168.1.1:-8', 'The port part of the value is not valid. It must be a number between 1 and 65535'])]
    #[TestWith(['192.168.1.1:0', 'The port part of the value is not valid. It must be a number between 1 and 65535'])]
    public function throwsWhenValueIsInvalid(string $value, string $expectedError): void
    {
        $this->expectException(MappingError::class);
        $this->expectExceptionMessage($expectedError);

        $this->converter->map($value, fn () => '');
    }

    #[Test]
    #[TestWith(['localhost'])]
    #[TestWith(['192.168.1.3'])]
    #[TestWith(['192.168.1.3:8080'])]
    #[TestWith(['example.com'])]
    #[TestWith(['example.com:8080'])]
    #[TestWith(['example.com:1'])]
    #[TestWith(['example.com:65535'])]
    #[TestWith(['subdomain.example.com'])]
    #[TestWith(['subdomain.example.com:8080'])]
    public function passesValidValuesToNextVerbatim(string $value): void
    {
        $calledArg = null;
        $next = function (mixed $arg) use (&$calledArg) {
            $calledArg = $arg;
            return $arg;
        };

        $this->converter->map($value, $next);

        self::assertEquals($calledArg, $value);
    }
}
