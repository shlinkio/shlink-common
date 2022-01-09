<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Validation;

use Laminas\Validator\AbstractValidator;

use function count;
use function Functional\contains;
use function is_array;

class OrderByValidator extends AbstractValidator
{
    public const VALID_ORDER_DIRS = ['ASC', 'DESC'];

    private const INVALID_TYPE = 'INVALID_TYPE';
    private const INVALID_ORDER_FIELD = 'INVALID_ORDER_FIELD';
    private const INVALID_ORDER_DIR = 'INVALID_ORDER_DIR';

    protected array $messageTemplates = [
        self::INVALID_TYPE => 'Provided value is not an array or does not have at least 2 elements.',
        self::INVALID_ORDER_FIELD => 'Resolved order field is not one of the supported ones.',
        self::INVALID_ORDER_DIR => 'Resolved order direction has to be one of ["ASC", "DESC"].',
    ];

    /**
     * @param string[] $validFields
     */
    public function __construct(private array $validFields)
    {
        parent::__construct([]);
    }

    public function isValid(mixed $value): bool
    {
        if (! is_array($value) || count($value) < 2) {
            $this->error(self::INVALID_TYPE);
            return false;
        }

        [$field, $dir] = $value;
        if ($field !== null && ! contains($this->validFields, $field)) {
            $this->error(self::INVALID_ORDER_FIELD);
            return false;
        }

        if ($dir !== null && ! contains(self::VALID_ORDER_DIRS, $dir)) {
            $this->error(self::INVALID_ORDER_DIR);
            return false;
        }

        return true;
    }
}
