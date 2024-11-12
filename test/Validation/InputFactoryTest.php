<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Validation;

use Laminas\Filter;
use Laminas\InputFilter\Input;
use Laminas\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Shlinkio\Shlink\Common\Validation\ExcludingValidatorChain;
use Shlinkio\Shlink\Common\Validation\InputFactory;
use Shlinkio\Shlink\Common\Validation\OrderByFilter;
use Shlinkio\Shlink\Common\Validation\OrderByValidator;

use function array_map;
use function get_class;
use function gettype;
use function is_object;

class InputFactoryTest extends TestCase
{
    #[Test, DataProvider('provideInputArgs')]
    public function basicInputIsCreatedWithDefaultFilters(array $args, bool $required): void
    {
        $input = InputFactory::basic(...$args);
        $filters = $this->getFiltersFromInput($input);

        self::assertEquals($required, $input->isRequired());
        self::assertCount(2, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);
    }

    #[Test, DataProvider('provideInputArgs')]
    public function arrayInputIsCreatedWithDefaultFilters(array $args, bool $required): void
    {
        $input = InputFactory::array(...$args);
        $filters = $this->getFiltersFromInput($input);

        self::assertEquals($required, $input->isRequired());
        self::assertCount(2, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);
    }

    #[Test, DataProvider('provideInputArgs')]
    public function booleanInputIsCreatedAsExpected(array $args, bool $required): void
    {
        $input = InputFactory::boolean(...$args);
        $filters = $this->getFiltersFromInput($input);
        $validators = $input->getValidatorChain()->getValidators();

        self::assertEquals($required, $input->isRequired());
        self::assertCount(1, $validators);
        self::assertCount(3, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);
        self::assertContains(Filter\Boolean::class, $filters);

        $notEmptyValidator = $validators[0]['instance'];
        $calculateTypeValue = (fn (array $type) => $this->calculateTypeValue($type))->bindTo( // @phpstan-ignore-line
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

    #[Test, DataProvider('provideInputArgs')]
    public function dateInputIsCreatedAsExpected(array $args, bool $required): void
    {
        $input = InputFactory::date(...$args);
        $filters = $this->getFiltersFromInput($input);
        $validators = $input->getValidatorChain()->getValidators();

        self::assertEquals($required, $input->isRequired());
        self::assertCount(1, $validators);
        self::assertCount(2, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);

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

    #[Test, DataProvider('provideInputArgs')]
    public function numericInputIsCreatedAsExpected(array $args, bool $required): void
    {
        $input = InputFactory::numeric(...$args);
        $filters = $this->getFiltersFromInput($input);
        $validators = $input->getValidatorChain()->getValidators();

        self::assertEquals($required, $input->isRequired());
        self::assertCount(2, $validators);
        self::assertCount(2, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);
        self::assertInstanceOf(Validator\Callback::class, $validators[0]['instance']);
        self::assertInstanceOf(Validator\GreaterThan::class, $validators[1]['instance']);
    }

    #[Test, DataProvider('provideInputArgs')]
    public function tagsInputIsCreatedAsExpected(array $args, bool $required): void
    {
        $input = InputFactory::tags(...$args);
        $filters = $this->getFiltersFromInput($input);
        $validators = $input->getValidatorChain()->getValidators();

        self::assertEquals($required, $input->isRequired());
        self::assertCount(0, $validators);
        self::assertCount(4, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);
        self::assertContains(Filter\StringToLower::class, $filters);
        self::assertContains(Filter\PregReplace::class, $filters);
    }

    public static function provideInputArgs(): iterable
    {
        yield [['foo', 'required' => true], true];
        yield [['foo', 'required' => false], false];
        yield [['foo'], false];
    }

    #[Test]
    public function orderByInputIsCreatedAsExpected(): void
    {
        $input = InputFactory::orderBy('orderBy', ['foo', 'bar', 'somethingElse']);
        $filters = $this->getFiltersFromInput($input);
        $validators = $input->getValidatorChain()->getValidators();

        self::assertCount(1, $validators);
        self::assertCount(3, $filters);
        self::assertContains(Filter\StripTags::class, $filters);
        self::assertContains(Filter\StringTrim::class, $filters);
        self::assertContains(OrderByFilter::class, $filters);
        self::assertInstanceOf(OrderByValidator::class, $validators[0]['instance']);
    }

    private function getFiltersFromInput(Input $input): array
    {
        return array_map(
            static fn (mixed $filter): string => is_object($filter) ? get_class($filter) : gettype($filter),
            $input->getFilterChain()->getFilters()->toArray(),
        );
    }
}
