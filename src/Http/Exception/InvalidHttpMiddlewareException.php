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
    /**
     * @param mixed $middleware
     */
    public static function fromRequestMiddleware($middleware): self
    {
        return static::fromMiddleware($middleware, 'request_middleware');
    }

    /**
     * @param mixed $middleware
     */
    public static function fromResponseMiddleware($middleware): self
    {
        return static::fromMiddleware($middleware, 'response_middleware');
    }

    /**
     * @param mixed $middleware
     */
    private static function fromMiddleware($middleware, string $type): self
    {
        return new self(sprintf(
            'Provided %s does not have a valid type. Expected callable, %s provided',
            $type,
            is_object($middleware) ? get_class($middleware) : getType($middleware),
        ));
    }
}
