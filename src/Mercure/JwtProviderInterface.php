<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use DateTimeImmutable;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

interface JwtProviderInterface extends TokenProviderInterface
{
    public function buildPublishToken(): string;

    public function buildSubscriptionToken(?DateTimeImmutable $expiresAt = null): string;
}
