<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger\Exception;

use Shlinkio\Shlink\Common\Exception\InvalidArgumentException;
use Shlinkio\Shlink\Common\Logger\LoggerType;

use function array_map;
use function implode;
use function sprintf;

class InvalidLoggerException extends InvalidArgumentException
{
    public static function fromInvalidName(string $name): self
    {
        return new self(sprintf(
            'Provided logger with name "%s" is not valid. Make sure to provide a value defined under the "logger" '
            . 'config key.',
            $name,
        ));
    }

    public static function fromInvalidType(string $type): self
    {
        return new self(sprintf(
            'Provided logger type "%s" is not valid. Expected one of ["%s"]',
            $type,
            implode('", "', array_map(static fn (LoggerType $type) => $type->value, LoggerType::cases())),
        ));
    }

    public static function fromInvalidFormatterType(string $type): self
    {
        return new self(sprintf(
            'Provided formatter type "%s" is not valid. Expected one of ["console", "json"]',
            $type,
        ));
    }
}
