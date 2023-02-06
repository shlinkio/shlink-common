<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Validation;

use Laminas\Validator;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Validation\ExcludingValidatorChain;

class ExcludingValidatorChainTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideValidatorsLists
     */
    public function validationPassesAsSoonAsOneWrappedValidatorPasses(
        array $validators,
        mixed $value,
        bool $expected,
    ): void {
        $chain = new ExcludingValidatorChain(...$validators);

        self::assertEquals($expected, $chain->isValid($value));
        self::assertEquals($expected, ($chain)($value));
    }

    public static function provideValidatorsLists(): iterable
    {
        yield [
            [
                new Validator\Between(['min' => 10, 'max' => 100]),
                new Validator\Between(['min' => 50, 'max' => 60]),
            ],
            80,
            true,
        ];
        yield [
            [
                new Validator\Between(['min' => 10, 'max' => 100]),
                new Validator\Digits(),
            ],
            5,
            true,
        ];
        yield [
            [
                new Validator\Between(['min' => 10, 'max' => 100]),
                new Validator\Between(['min' => 50, 'max' => 60]),
            ],
            'foo',
            false,
        ];
    }

    /** @test */
    public function messagesFromAllNonPassingValidatorsAreWrappedUntilOnePasses(): void
    {
        $chain = new ExcludingValidatorChain(
            new Validator\EmailAddress(),
            new Validator\Between(['min' => 50, 'max' => 60]),
            new Validator\Digits(),
        );

        self::assertTrue($chain->isValid(1000));
        self::assertEquals([
            Validator\EmailAddress::INVALID => 'Invalid type given. String expected',
            Validator\Between::NOT_BETWEEN => 'The input is not between \'50\' and \'60\', inclusively',
        ], $chain->getMessages());
    }
}
