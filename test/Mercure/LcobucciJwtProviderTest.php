<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use Cake\Chronos\Chronos;
use Lcobucci\JWT\Configuration;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Mercure\LcobucciJwtProvider;

class LcobucciJwtProviderTest extends TestCase
{
    private Configuration $jwtConfig;

    public function setUp(): void
    {
        $this->jwtConfig = Configuration::forUnsecuredSigner();
    }

    /**
     * @test
     * @dataProvider provideMercureConfigs
     */
    public function expectedTokenIsCreated(
        array $mercureConfig,
        string $expectedIssuer,
        ?Chronos $expiresAt,
        Chronos $expectedExpiration
    ): void {
        $token = $this->jwtConfig->getParser()->parse(
            (new LcobucciJwtProvider($this->jwtConfig, $mercureConfig))($expiresAt),
        );

        $this->assertTrue($token->hasBeenIssuedBy($expectedIssuer));
        $this->assertTrue($token->isExpired($expectedExpiration->addSeconds(5)));
    }

    public function provideMercureConfigs(): iterable
    {
        yield 'without issuer' => [[], 'Shlink', null, Chronos::now()->addMinutes(10)];
        yield 'with issuer' => [
            ['jwt_issuer' => $issuer = 'foobar'],
            $issuer,
            $expires = Chronos::now()->addMonths(5),
            $expires,
        ];
    }
}
