<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Exception;

use RuntimeException;

class MercureException extends RuntimeException implements ExceptionInterface
{
    public static function missingJwtSecret(): self
    {
        return new self('You have to provide a secret key for the JWT generation, under mercure.jwt_secret');
    }

    public static function missingHubUrl(): self
    {
        return new self('You have to provide mercure hub URL under mercure.internal_hub_url or mercure.public_hub_url');
    }
}
