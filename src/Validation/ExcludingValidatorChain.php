<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Validation;

use Laminas\Validator;

use function array_replace_recursive;

/**
 * A validator chain which is considered valid as soon as one of its validators is valid
 */
class ExcludingValidatorChain implements Validator\ValidatorInterface
{
    private array $validators;
    private array $messages = [];

    public function __construct(Validator\ValidatorInterface ...$validators)
    {
        $this->validators = $validators;
    }

    public function isValid(mixed $value): bool
    {
        foreach ($this->validators as $validator) {
            if ($validator->isValid($value)) {
                return true;
            }

            // Aggregate error messages from validators that failed until one succeeds
            $messages = $validator->getMessages();
            $this->messages = array_replace_recursive($this->messages, $messages);
        }

        return false;
    }

    public function __invoke(mixed $value): bool
    {
        return $this->isValid($value);
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
