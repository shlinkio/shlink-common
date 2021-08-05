<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Http\Exception;

use Shlinkio\Shlink\Common\Exception\InvalidArgumentException;

use function get_class;
use function getType;
use function is_object;
use function sprintf;

class InvalidHttpMiddlewareException extends InvalidArgumentException
{
    public static function fromMiddleware(mixed $middleware): self
    {
        return new self(sprintf(
            'Provided middleware does not have a valid type. Expected callable, %s provided',
            is_object($middleware) ? get_class($middleware) : getType($middleware),
        ));
    }
}
