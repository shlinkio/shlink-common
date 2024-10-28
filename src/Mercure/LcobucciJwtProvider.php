<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use Cake\Chronos\Chronos;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;

class LcobucciJwtProvider implements JwtProviderInterface
{
    public function __construct(
        private readonly Configuration $jwtConfig,
        private readonly MercureOptions $mercureOptions,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getJwt(): string
    {
        return $this->buildPublishToken();
    }

    /**
     * @return non-empty-string
     */
    public function buildPublishToken(): string
    {
        $expiresAt = $this->roundDateToTheSecond(Chronos::now()->addMinutes(10));
        return $this->buildToken(['publish' => ['*']], $expiresAt);
    }

    /**
     * @return non-empty-string
     */
    public function buildSubscriptionToken(DateTimeImmutable|null $expiresAt = null): string
    {
        $expiresAt = $this->roundDateToTheSecond($expiresAt ?? Chronos::now()->addDays(3));
        return $this->buildToken(['subscribe' => ['*']], $expiresAt);
    }

    /**
     * @return non-empty-string
     */
    private function buildToken(array $mercureClaim, DateTimeImmutable $expiresAt): string
    {
        $now = $this->roundDateToTheSecond(Chronos::now());

        return $this->jwtConfig
            ->builder()
            ->issuedBy($this->mercureOptions->jwtIssuer)
            ->issuedAt($now)
            ->expiresAt($expiresAt)
            ->withClaim('mercure', $mercureClaim)
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())
            ->toString();
    }

    private function roundDateToTheSecond(DateTimeImmutable $date): Chronos
    {
        // This removes the microseconds, rounding down to the second, and working around how Lcobucci\JWT parses dates
        return Chronos::parse($date->format('Y-m-d H:i:s'));
    }
}
