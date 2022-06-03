<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger\Exception;

use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Logger\Exception\InvalidLoggerException;

class InvalidLoggerExceptionTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideNames
     */
    public function fromInvalidNameBuildsExpectedException(string $name, string $expectedMessage): void
    {
        $e = InvalidLoggerException::fromInvalidName($name);
        self::assertEquals($expectedMessage, $e->getMessage());
    }

    public function provideNames(): iterable
    {
        yield [
            'foo',
            'Provided logger with name "foo" is not valid. Make sure to provide a value defined under the "logger" '
            . 'config key.',
        ];
        yield [
            'bar',
            'Provided logger with name "bar" is not valid. Make sure to provide a value defined under the "logger" '
            . 'config key.',
        ];
        yield [
            'my_logger',
            'Provided logger with name "my_logger" is not valid. Make sure to provide a value defined under the '
            . '"logger" config key.',
        ];
    }

    /**
     * @test
     * @dataProvider provideTypes
     */
    public function fromInvalidTypeBuildsExpectedException(string $type, string $expectedMessage): void
    {
        $e = InvalidLoggerException::fromInvalidType($type);
        self::assertEquals($expectedMessage, $e->getMessage());
    }

    public function provideTypes(): iterable
    {
        yield ['foo', 'Provided logger type "foo" is not valid. Expected one of ["file", "stream"]'];
        yield ['bar', 'Provided logger type "bar" is not valid. Expected one of ["file", "stream"]'];
        yield ['another_type', 'Provided logger type "another_type" is not valid. Expected one of ["file", "stream"]'];
    }
}
