<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use Cake\Chronos\Chronos;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\UnencryptedToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Mercure\LcobucciJwtProvider;

class LcobucciJwtProviderTest extends TestCase
{
    private Configuration $jwtConfig;

    public function setUp(): void
    {
        $this->jwtConfig = Configuration::forUnsecuredSigner();
    }

    #[Test, DataProvider('provideMercureConfigs')]
    public function expectedPublishTokenIsCreated(array $mercureConfig, string $expectedIssuer): void
    {
        /** @var UnencryptedToken $token */
        $token = $this->jwtConfig->parser()->parse(
            (new LcobucciJwtProvider($this->jwtConfig, $mercureConfig))->getJwt(),
        );

        self::assertTrue($token->hasBeenIssuedBy($expectedIssuer));
        self::assertTrue($token->isExpired(Chronos::now()->addMinutes(10)->addSeconds(5)));
        self::assertEquals(['publish' => ['*']], $token->claims()->get('mercure'));
    }

    public static function provideMercureConfigs(): iterable
    {
        yield 'without issuer' => [[], 'Shlink'];
        yield 'with issuer' => [['jwt_issuer' => $issuer = 'foobar'], $issuer];
    }

    #[Test, DataProvider('provideExpirationDates')]
    public function expectedSubscriptionTokenIsCreated(?Chronos $expiresAt, Chronos $expectedExpiresAt): void
    {
        /** @var UnencryptedToken $token */
        $token = $this->jwtConfig->parser()->parse(
            (new LcobucciJwtProvider($this->jwtConfig, []))->buildSubscriptionToken($expiresAt),
        );

        self::assertTrue($token->isExpired($expectedExpiresAt->addSeconds(5)));
        self::assertEquals(['subscribe' => ['*']], $token->claims()->get('mercure'));
    }

    public static function provideExpirationDates(): iterable
    {
        yield 'default expiration' => [null, Chronos::now()->addDays(3)];
        yield 'explicit expiration' => [$expires = Chronos::now()->addMonths(5), $expires];
    }
}
