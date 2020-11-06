<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Validation;

use Laminas\Validator\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Validation\HostAndPortValidator;
use stdClass;

use function array_values;
use function gettype;
use function sprintf;

class HostAndPortValidatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideInvalidValues
     */
    public function failsToValidateWhenProvidedDataIsInvalid(string $value, string $expectedError): void
    {
        $validator = new HostAndPortValidator();

        self::assertFalse($validator->isValid($value));
        self::assertContains($expectedError, array_values($validator->getMessages()));
    }

    public function provideInvalidValues(): iterable
    {
        yield ['foo:bar:baz', 'Provided value, once split using the ":" separator, returned more than 2 parts'];
        yield ['foo:bar:baz:foo', 'Provided value, once split using the ":" separator, returned more than 2 parts'];
        yield ['$%&/', 'The host part of the value is not valid'];
        yield ['192.168.1.20', 'The host part of the value is not valid'];
        yield ['8.8.8.8', 'The host part of the value is not valid'];
        yield ['example.com:80000', 'The port part of the value is not valid. Must be a number between 1 and 65535'];
        yield ['example.com:65536', 'The port part of the value is not valid. Must be a number between 1 and 65535'];
        yield ['example.com:0', 'The port part of the value is not valid. Must be a number between 1 and 65535'];
        yield ['example.com:10.4', 'The port part of the value is not valid. Must be a number between 1 and 65535'];
        yield ['example.com:-1', 'The port part of the value is not valid. Must be a number between 1 and 65535'];
        yield ['example.com:-2000', 'The port part of the value is not valid. Must be a number between 1 and 65535'];
    }

    /**
     * @test
     * @dataProvider provideValidValues
     */
    public function succeedsWhenProvidingValidValues(string $value): void
    {
        $validator = new HostAndPortValidator();
        self::assertTrue($validator->isValid($value));
    }

    public function provideValidValues(): iterable
    {
        yield ['localhost'];
        yield ['localhost:3000'];
        yield ['example.com'];
        yield ['example.com:8080'];
        yield ['example.com:1'];
        yield ['example.com:65535'];
    }

    /**
     * @param mixed $value
     * @test
     * @dataProvider provideIncompatibleValues
     */
    public function throwsExceptionWhenProvidedValuesIsNotString($value, string $expectedExceptionMessage): void
    {
        $validator = new HostAndPortValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $validator->isValid($value);
    }

    public function provideIncompatibleValues(): iterable
    {
        yield [new stdClass(), sprintf('Expected value to be a string. %s provided', stdClass::class)];
        yield [1, sprintf('Expected value to be a string. %s provided', gettype(1))];
        yield [1.1, sprintf('Expected value to be a string. %s provided', gettype(1.1))];
        yield [false, sprintf('Expected value to be a string. %s provided', gettype(false))];
    }
}
