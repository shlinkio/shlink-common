<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use DateTimeImmutable;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

interface JwtProviderInterface extends TokenProviderInterface
{
    /**
     * @return non-empty-string
     */
    public function buildPublishToken(): string;

    /**
     * @return non-empty-string
     */
    public function buildSubscriptionToken(?DateTimeImmutable $expiresAt = null): string;
}
