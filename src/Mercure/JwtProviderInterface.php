<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use DateTimeImmutable;

interface JwtProviderInterface
{
    public function buildPublishToken(): string;

    public function buildSubscriptionToken(?DateTimeImmutable $expiresAt = null): string;
}
