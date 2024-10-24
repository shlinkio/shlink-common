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
        public ?string $publicHubUrl = null,
        public ?string $internalHubUrl = null,
        public ?string $jwtSecret = null,
        public string $jwtIssuer = 'Shlink',
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->publicHubUrl !== null && trim($this->publicHubUrl) !== '';
    }
}
