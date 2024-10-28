<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Validation;

use DateTimeInterface;
use Laminas\Filter;
use Laminas\InputFilter\ArrayInput;
use Laminas\InputFilter\Input;
use Laminas\Validator;

use function array_map;
use function is_numeric;

final class InputFactory
{
    public static function basic(string $name, bool $required = false): Input
    {
        $input = new Input($name);
        $input->setRequired($required)
              ->getFilterChain()->attach(new Filter\StripTags())
                                ->attach(new Filter\StringTrim());
        return $input;
    }

    public static function boolean(string $name, bool $required = false): Input
    {
        $input = self::basic($name, $required);
        $input->getFilterChain()->attach(new Filter\Boolean([
            'type' => Filter\Boolean::TYPE_PHP | Filter\Boolean::TYPE_FALSE_STRING,
        ]));
        $input->getValidatorChain()->attach(new Validator\NotEmpty(['type' => [
            Validator\NotEmpty::OBJECT,
            Validator\NotEmpty::SPACE,
            Validator\NotEmpty::NULL,
            Validator\NotEmpty::EMPTY_ARRAY,
            Validator\NotEmpty::STRING,
        ]]));

        return $input;
    }

    public static function date(
        string $name,
        array $formats = [DateTimeInterface::ATOM, 'Y-m-d'],
        bool $required = false,
    ): Input {
        $input = self::basic($name, $required);
        $input->getValidatorChain()->attach(new ExcludingValidatorChain(...array_map(
            fn (string $format) => new Validator\Date(['format' => $format]),
            $formats,
        )));
        return $input;
    }

    public static function numeric(string $name, int|null $min = 1, bool $required = false): Input
    {
        $input = self::basic($name, $required);
        $input->getValidatorChain()->attach(new Validator\Callback(fn ($value) => is_numeric($value)))
                                   ->attach(new Validator\GreaterThan(['min' => $min, 'inclusive' => true]));

        return $input;
    }

    public static function array(string $name, bool $required = false): ArrayInput
    {
        $input = new ArrayInput($name);
        $input->setRequired($required)
              ->getFilterChain()->attach(new Filter\StripTags())
                                ->attach(new Filter\StringTrim());
        return $input;
    }

    public static function tags(string $name, bool $required = false): ArrayInput
    {
        $tags = self::array($name, $required);
        $tags->getFilterChain()->attach(new Filter\StringToLower())
                               ->attach(new Filter\PregReplace(['pattern' => '/ /', 'replacement' => '-']));

        return $tags;
    }

    /**
     * @param string[] $validFields
     */
    public static function orderBy(string $name, array $validFields, bool $required = false): Input
    {
        $input = self::basic($name, $required);
        $input->getFilterChain()->attach(new OrderByFilter());
        $input->getValidatorChain()->attach(new OrderByValidator($validFields));

        return $input;
    }
}
