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
        return (string) $this->jwtConfig
            ->createBuilder()
            ->issuedBy($this->mercureConfig['jwt_issuer'] ?? 'Shlink')
            ->issuedAt(Chronos::now())
            ->expiresAt($expiresAt ?? Chronos::now()->addMinutes(10))
            ->getToken($this->jwtConfig->getSigner(), $this->jwtConfig->getSigningKey());
    }
}
