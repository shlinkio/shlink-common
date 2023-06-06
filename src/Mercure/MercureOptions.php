<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

class MercureOptions
{
    /**
     * @param non-empty-string $jwtIssuer
     */
    public function __construct(
        public readonly ?string $publicHubUrl = null,
        public readonly ?string $internalHubUrl = null,
        public readonly ?string $jwtSecret = null,
        public readonly string $jwtIssuer = 'Shlink',
    ) {
    }
}
