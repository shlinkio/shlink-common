<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Validation;

use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Validation\OrderByValidator;
use stdClass;

class OrderByValidatorTest extends TestCase
{
    private OrderByValidator $validator;

    public function setUp(): void
    {
        $this->validator = new OrderByValidator(['foo', 'bar', 'somethingElse']);
    }

    /**
     * @test
     * @dataProvider provideInvalidValues
     */
    public function expectedErrorIsReturnedIfValueIsNotValid(mixed $value, array $expectedErrors): void
    {
        self::assertFalse($this->validator->isValid($value));
        self::assertEquals($expectedErrors, $this->validator->getMessages());
    }

    public static function provideInvalidValues(): iterable
    {
        $invalidTypeError = ['INVALID_TYPE' => 'Provided value is not an array or does not have at least 2 elements.'];

        yield ['', $invalidTypeError];
        yield [null, $invalidTypeError];
        yield [3, $invalidTypeError];
        yield [new stdClass(), $invalidTypeError];
        yield [[], $invalidTypeError];
        yield [['foo'], $invalidTypeError];
        yield [['invalid', 'ASC'], ['INVALID_ORDER_FIELD' => 'Resolved order field is not one of the supported ones.']];
        yield [['foo', 'bar'], ['INVALID_ORDER_DIR' => 'Resolved order direction has to be one of ["ASC", "DESC"].']];
    }

    /**
     * @test
     * @dataProvider provideValidValues
     */
    public function successIsReturnedIfValueIsValid(array $value): void
    {
        self::assertTrue($this->validator->isValid($value));
    }

    public static function provideValidValues(): iterable
    {
        yield [['foo', 'ASC']];
        yield [['foo', 'DESC']];
        yield [['foo', null]];
        yield [['bar', 'ASC']];
        yield [['bar', 'DESC']];
        yield [['bar', null]];
        yield [['somethingElse', 'ASC']];
        yield [['somethingElse', 'DESC']];
        yield [['somethingElse', null]];
        yield [[null, 'ASC']];
        yield [[null, 'DESC']];
        yield [[null, null]];
    }
}
