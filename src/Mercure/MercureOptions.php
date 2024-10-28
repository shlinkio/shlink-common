<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use function trim;

readonly class MercureOptions
{
    /**
     * @param non-empty-string $jwtIssuer
     */
    public function __construct(
        public string|null $publicHubUrl = null,
        public string|null $internalHubUrl = null,
        public string|null $jwtSecret = null,
        public string $jwtIssuer = 'Shlink',
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->publicHubUrl !== null && trim($this->publicHubUrl) !== '';
    }
}
