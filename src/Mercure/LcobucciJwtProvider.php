<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use Cake\Chronos\Chronos;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;

class LcobucciJwtProvider
{
    private Configuration $jwtConfig;
    private array $mercureConfig;

    public function __construct(Configuration $jwtConfig, array $mercureConfig)
    {
        $this->jwtConfig = $jwtConfig;
        $this->mercureConfig = $mercureConfig;
    }

    public function __invoke(?DateTimeImmutable $expiresAt = null): string
    {
        $now = $this->roundDateToCurrentSecond(Chronos::now());
        $expiresAt = $expiresAt === null ? $now->addMinutes(10) : $this->roundDateToCurrentSecond($expiresAt);

        return (string) $this->jwtConfig
            ->createBuilder()
            ->issuedBy($this->mercureConfig['jwt_issuer'] ?? 'Shlink')
            ->issuedAt($now)
            ->expiresAt($expiresAt)
            ->getToken($this->jwtConfig->getSigner(), $this->jwtConfig->getSigningKey());
    }

    public function roundDateToCurrentSecond(DateTimeImmutable $date): Chronos
    {
        // This removes the microseconds, rounding down to the second, and working around how Lcobucci\JWT parses dates
        return Chronos::parse($date->format('Y-m-d h:i:s'));
    }
}
