<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Validation;

use DateTime;
use Laminas\Filter;
use Laminas\InputFilter\ArrayInput;
use Laminas\InputFilter\Input;
use Laminas\Validator;

use function Functional\map;

trait InputFactoryTrait
{
    private function createInput(string $name, bool $required = true): Input
    {
        $input = new Input($name);
        $input->setRequired($required)
              ->getFilterChain()->attach(new Filter\StripTags())
                                ->attach(new Filter\StringTrim());
        return $input;
    }

    private function createBooleanInput(string $name, bool $required = true): Input
    {
        $input = $this->createInput($name, $required);
        $input->getFilterChain()->attach(new Filter\Boolean());
        $input->getValidatorChain()->attach(new Validator\NotEmpty(['type' => [
            Validator\NotEmpty::OBJECT,
            Validator\NotEmpty::SPACE,
            Validator\NotEmpty::NULL,
            Validator\NotEmpty::EMPTY_ARRAY,
            Validator\NotEmpty::STRING,
        ]]));

        return $input;
    }

    private function createDateInput(
        string $name,
        bool $required = true,
        array $formats = [DateTime::ATOM, 'Y-m-d']
    ): Input {
        $input = $this->createInput($name, $required);
        $input->getValidatorChain()->attach(new ExcludingValidatorChain(...map(
            $formats,
            fn (string $format) => new Validator\Date(['format' => $format]),
        )));
        return $input;
    }

    private function createArrayInput(string $name, bool $required = true): Input
    {
        $input = new ArrayInput($name);
        $input->setRequired($required)
              ->getFilterChain()->attach(new Filter\StripTags())
                                ->attach(new Filter\StringTrim());
        return $input;
    }
}
