<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Validation;

use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Validation\InputFactoryTrait;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\Validator;

use function Functional\map;
use function get_class;

class InputFactoryTraitTest extends TestCase
{
    use InputFactoryTrait;

    /**
     * @test
     * @dataProvider provideRequired
     */
    public function basicInputIsCreatedWithDefaultFilters(bool $required): void
    {
        $input = $this->createInput('foo', $required);
        $filters = $this->getFiltersFromInput($input);

        $this->assertEquals($required, $input->isRequired());
        $this->assertCount(2, $filters);
        $this->assertContains(Filter\StripTags::class, $filters);
        $this->assertContains(Filter\StringTrim::class, $filters);
    }

    /**
     * @test
     * @dataProvider provideRequired
     */
    public function booleanInputIsCreatedAsExpected(bool $required): void
    {
        $input = $this->createBooleanInput('foo', $required);
        $filters = $this->getFiltersFromInput($input);
        $validators = $input->getValidatorChain()->getValidators();

        $this->assertEquals($required, $input->isRequired());
        $this->assertCount(1, $validators);
        $this->assertCount(3, $filters);
        $this->assertContains(Filter\StripTags::class, $filters);
        $this->assertContains(Filter\StringTrim::class, $filters);
        $this->assertContains(Filter\Boolean::class, $filters);

        /** @var Validator\NotEmpty $notEmptyValidator */
        $notEmptyValidator = $validators[0]['instance'];
        $calculateTypeValue = (fn (array $type) => $this->calculateTypeValue($type))->bindTo(
            $notEmptyValidator,
            Validator\NotEmpty::class,
        );
        $this->assertInstanceOf(Validator\NotEmpty::class, $notEmptyValidator);
        $this->assertEquals($calculateTypeValue([
            Validator\NotEmpty::OBJECT,
            Validator\NotEmpty::SPACE,
            Validator\NotEmpty::NULL,
            Validator\NotEmpty::EMPTY_ARRAY,
            Validator\NotEmpty::STRING,
        ]), $notEmptyValidator->getType());
    }

    public function provideRequired(): iterable
    {
        yield [true];
        yield [false];
    }

    private function getFiltersFromInput(Input $input): array
    {
        return map(
            $input->getFilterChain()->getFilters()->toArray(),
            fn (Filter\FilterInterface $filter): string => get_class($filter),
        );
    }
}
