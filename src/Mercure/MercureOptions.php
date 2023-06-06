<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use function trim;

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

    public function isEnabled(): bool
    {
        return $this->publicHubUrl !== null && trim($this->publicHubUrl) !== '';
    }
}
