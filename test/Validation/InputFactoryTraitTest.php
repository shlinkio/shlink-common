<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Validation;

use Laminas\Filter;
use Laminas\InputFilter\Input;
use Laminas\Validator;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Shlinkio\Shlink\Common\Validation\ExcludingValidatorChain;
use Shlinkio\Shlink\Common\Validation\InputFactoryTrait;

use function Functional\map;
use function get_class;

class InputFactoryTraitTest extends TestCase
{
    use InputFactoryTrait;

    /**
     * @test
     * @dataProvider provideInputArgs
     */
    public function basicInputIsCreatedWithDefaultFilters(array $args, bool $required): void
    {
        $input = $this->createInput(...$args);
        $filters = $this->getFiltersFromInput($input);

        self::assertEquals($required, $input->isRequired());
        self::assertCount(2, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);
    }

    /**
     * @test
     * @dataProvider provideInputArgs
     */
    public function arrayInputIsCreatedWithDefaultFilters(array $args, bool $required): void
    {
        $input = $this->createArrayInput(...$args);
        $filters = $this->getFiltersFromInput($input);

        self::assertEquals($required, $input->isRequired());
        self::assertCount(2, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);
    }

    /**
     * @test
     * @dataProvider provideInputArgs
     */
    public function booleanInputIsCreatedAsExpected(array $args, bool $required): void
    {
        $input = $this->createBooleanInput(...$args);
        $filters = $this->getFiltersFromInput($input);
        $validators = $input->getValidatorChain()->getValidators();

        self::assertEquals($required, $input->isRequired());
        self::assertCount(1, $validators);
        self::assertCount(3, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);
        self::assertContains(Filter\Boolean::class, $filters);

        /** @var Validator\NotEmpty $notEmptyValidator */
        $notEmptyValidator = $validators[0]['instance'];
        $calculateTypeValue = (fn (array $type) => $this->calculateTypeValue($type))->bindTo(
            $notEmptyValidator,
            Validator\NotEmpty::class,
        );
        self::assertInstanceOf(Validator\NotEmpty::class, $notEmptyValidator);
        self::assertEquals($calculateTypeValue([
            Validator\NotEmpty::OBJECT,
            Validator\NotEmpty::SPACE,
            Validator\NotEmpty::NULL,
            Validator\NotEmpty::EMPTY_ARRAY,
            Validator\NotEmpty::STRING,
        ]), $notEmptyValidator->getType());
    }

    /**
     * @test
     * @dataProvider provideInputArgs
     */
    public function dateInputIsCreatedAsExpected(array $args, bool $required): void
    {
        $input = $this->createDateInput(...$args);
        $filters = $this->getFiltersFromInput($input);
        $validators = $input->getValidatorChain()->getValidators();

        self::assertEquals($required, $input->isRequired());
        self::assertCount(1, $validators);
        self::assertCount(2, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);

        /** @var ExcludingValidatorChain $excludingValidator */
        $excludingValidator = $validators[0]['instance'];
        self::assertInstanceOf(ExcludingValidatorChain::class, $excludingValidator);

        $ref = new ReflectionObject($excludingValidator);
        $prop = $ref->getProperty('validators');
        $prop->setAccessible(true);
        $validators = $prop->getValue($excludingValidator);
        self::assertCount(2, $validators);
        self::assertInstanceOf(Validator\Date::class, $validators[0]);
        self::assertInstanceOf(Validator\Date::class, $validators[1]);
    }

    public function provideInputArgs(): iterable
    {
        yield [['foo', true], true];
        yield [['foo', false], false];
        yield [['foo'], true];
    }

    private function getFiltersFromInput(Input $input): array
    {
        return map(
            $input->getFilterChain()->getFilters()->toArray(),
            fn (Filter\FilterInterface $filter): string => get_class($filter),
        );
    }
}
