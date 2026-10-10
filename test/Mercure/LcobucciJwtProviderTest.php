<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use Cake\Chronos\Chronos;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\UnencryptedToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Mercure\LcobucciJwtProvider;
use Shlinkio\Shlink\Common\Mercure\MercureOptions;
use Shlinkio\Shlink\Common\Mercure\MercureVersion;

class LcobucciJwtProviderTest extends TestCase
{
    private Configuration $jwtConfig;

    public function setUp(): void
    {
        $this->jwtConfig = Configuration::forSymmetricSigner(
            new class implements Signer {
                public function algorithmId(): string
                {
                    return 'none';
                }

                public function sign(string $payload, Key $key): string
                {
                    return 'test_shlink';
                }

                public function verify(string $expected, string $payload, Key $key): bool
                {
                    return $expected === 'test_shlink';
                }
            },
            new class implements Key {
                public function contents(): string
                {
                    return 'empty';
                }

                public function passphrase(): string
                {
                    return 'empty';
                }
            },
        );
    }

    /**
     * @param non-empty-string $expectedIssuer
     */
    #[Test, DataProvider('provideMercureConfigs')]
    public function expectedPublishTokenIsCreated(MercureOptions $mercureOptions, string $expectedIssuer): void
    {
        /** @var UnencryptedToken $token */
        $token = $this->jwtConfig
            ->parser()
            ->parse(
                new LcobucciJwtProvider($this->jwtConfig, $mercureOptions)->getJwt(),
            );

        self::assertTrue($token->hasBeenIssuedBy($expectedIssuer));
        self::assertTrue($token->isExpired(Chronos::now()->addMinutes(10)->addSeconds(5)));
    }

    public static function provideMercureConfigs(): iterable
    {
        yield 'without issuer' => [new MercureOptions(), 'Shlink'];
        yield 'with issuer' => [new MercureOptions(jwtIssuer: $issuer = 'foobar'), $issuer];
    }

    #[Test, DataProvider('provideExpirationDates')]
    public function expectedExpirationIsSetInSubscriptionToken(
        Chronos|null $expiresAt,
        Chronos $expectedExpiresAt,
    ): void {
        /** @var UnencryptedToken $token */
        $token = $this->jwtConfig
            ->parser()
            ->parse(
                new LcobucciJwtProvider($this->jwtConfig, new MercureOptions())->buildSubscriptionToken($expiresAt),
            );

        self::assertTrue($token->isExpired($expectedExpiresAt->addSeconds(5)));
    }

    public static function provideExpirationDates(): iterable
    {
        yield 'default expiration' => [null, Chronos::now()->addDays(3)];
        yield 'explicit expiration' => [$expires = Chronos::now()->addMonths(5), $expires];
    }

    #[Test]
    #[TestWith([MercureVersion::v0])]
    #[TestWith([MercureVersion::v1])]
    public function extraHeadersAndClaimsAreAddedForVersionOne(MercureVersion $version): void
    {
        /** @var UnencryptedToken $token */
        $token = $this->jwtConfig
            ->parser()
            ->parse(
                new LcobucciJwtProvider(
                    $this->jwtConfig,
                    new MercureOptions(version: $version),
                )->buildSubscriptionToken(),
            );
        $claims = $token->claims();

        if ($version === MercureVersion::v0) {
            self::assertEquals('JWT', $token->headers()->get('typ'));
            self::assertFalse($claims->has('aud'));
            self::assertFalse($claims->has('authorization_details'));
            self::assertTrue($claims->has('mercure'));
        } else {
            self::assertEquals('at+jwt', $token->headers()->get('typ'));
            self::assertTrue($claims->has('aud'));
            self::assertTrue($claims->has('authorization_details'));
            self::assertFalse($claims->has('mercure'));
        }
    }
}
