<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Validation;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Between;
use Zend\Validator\Digits;
use Zend\Validator\Exception;
use Zend\Validator\Hostname;
use Zend\Validator\ValidatorChain;
use Zend\Validator\ValidatorInterface;

use function count;
use function explode;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;

class HostAndPortValidator extends AbstractValidator
{
    private const INVALID_AMOUNT_OF_PARTS = 'INVALID_AMOUNT_OF_PARTS';
    private const INVALID_HOST = 'INVALID_HOST';
    private const INVALID_PORT = 'INVALID_PORT';

    protected array $messageTemplates = [
        self::INVALID_AMOUNT_OF_PARTS =>
            'Provided value, once split using the ":" separator, returned more than 2 parts',
        self::INVALID_HOST => 'The host part of the value is not valid',
        self::INVALID_PORT => 'The port part of the value is not valid. Must be a number between 1 and 65535',
    ];

    private ValidatorInterface $hostValidator;
    private ValidatorInterface $portValidator;

    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->hostValidator = new Hostname([
            'allow' => Hostname::ALLOW_DNS | Hostname::ALLOW_LOCAL,
        ]);
        $this->portValidator = (new ValidatorChain())->attach(new Digits())
                                                     ->attach(new Between([
                                                         'min' => 1,
                                                         'max' => 65535,
                                                         'inclusive' => true,
                                                     ]));
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value): bool
    {
        if (! is_string($value)) {
            throw new Exception\RuntimeException(sprintf(
                'Expected value to be a string. %s provided',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        $parts = explode(':', $value);
        if (count($parts) > 2) {
            $this->error(self::INVALID_AMOUNT_OF_PARTS);
            return false;
        }

        if (! $this->hostValidator->isValid($parts[0])) {
            $this->error(self::INVALID_HOST);
            return false;
        }

        if (isset($parts[1]) && ! $this->portValidator->isValid($parts[1])) {
            $this->error(self::INVALID_PORT);
            return false;
        }

        return true;
    }
}
