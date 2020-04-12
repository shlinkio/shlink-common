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
    public function expectedPublishTokenIsCreated(array $mercureConfig, string $expectedIssuer): void
    {
        $token = $this->jwtConfig->getParser()->parse(
            (new LcobucciJwtProvider($this->jwtConfig, $mercureConfig))(),
        );

        $this->assertTrue($token->hasBeenIssuedBy($expectedIssuer));
        $this->assertTrue($token->isExpired(Chronos::now()->addMinutes(10)->addSeconds(5)));
        $this->assertEquals(['publish' => []], $token->claims()->get('mercure'));
    }

    public function provideMercureConfigs(): iterable
    {
        yield 'without issuer' => [[], 'Shlink'];
        yield 'with issuer' => [['jwt_issuer' => $issuer = 'foobar'], $issuer];
    }

    /**
     * @test
     * @dataProvider provideExpirationDates
     */
    public function expectedSubscriptionTokenIsCreated(?Chronos $expiresAt, Chronos $expectedExpiresAt): void
    {
        $token = $this->jwtConfig->getParser()->parse(
            (new LcobucciJwtProvider($this->jwtConfig, []))->buildSubscriptionToken($expiresAt),
        );

        $this->assertTrue($token->isExpired($expectedExpiresAt->addSeconds(5)));
        $this->assertEquals(['subscribe' => []], $token->claims()->get('mercure'));
    }

    public function provideExpirationDates(): iterable
    {
        yield 'default expiration' => [null, Chronos::now()->addDays(3)];
        yield 'explicit expiration' => [$expires = Chronos::now()->addMonths(5), $expires];
    }
}
