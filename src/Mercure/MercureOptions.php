<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

readonly class MercureOptions
{
    public string $authCookieName;

    /**
     * @param non-empty-string $jwtIssuer
     */
    public function __construct(
        public bool $enabled = false,
        public string|null $publicHubUrl = null,
        public string|null $internalHubUrl = null,
        public string|null $jwtSecret = null,
        public string $jwtIssuer = 'Shlink',
        public MercureVersion $version = MercureVersion::LEGACY,
        string|null $authCookieName = null,
    ) {
        $this->authCookieName =
            $authCookieName
            // These are mercure default cookie names for legacy and stable versions.
            // See https://mercure.rocks/docs/UPGRADE#what-changed-at-a-glance
            ?? ($this->version === MercureVersion::LEGACY ? 'mercureAuthorization' : '__Secure-mercure_access_token');
    }

    /** @deprecated Use `enabled` prop directly instead */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
