<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Validation;

use Laminas\Validator;

use function array_replace_recursive;
use function Functional\some;

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
        return some(
            $this->validators,
            function (Validator\ValidatorInterface $validator) use ($value): bool {
                if ($validator->isValid($value)) {
                    return true;
                }

                $messages = $validator->getMessages();
                $this->messages = array_replace_recursive($this->messages, $messages);
                return false;
            },
        );
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
