<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

readonly class MercureOptions
{
    /**
     * @param non-empty-string $jwtIssuer
     */
    public function __construct(
        public bool $enabled = false,
        public string|null $publicHubUrl = null,
        public string|null $internalHubUrl = null,
        public string|null $jwtSecret = null,
        public string $jwtIssuer = 'Shlink',
    ) {
    }

    /** @deprecated Use `enabled` prop directly instead */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
