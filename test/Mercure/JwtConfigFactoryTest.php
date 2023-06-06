<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use Lcobucci\JWT\Signer\Hmac\Sha256;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Exception\MercureException;
use Shlinkio\Shlink\Common\Mercure\JwtConfigFactory;
use Shlinkio\Shlink\Common\Mercure\MercureOptions;

class JwtConfigFactoryTest extends TestCase
{
    private JwtConfigFactory $factory;
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->factory = new JwtConfigFactory();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    #[Test, DataProvider('provideInvalidConfigs')]
    public function throwsExceptionWhenProperConfigCouldNotBeFound(MercureOptions $options): void
    {
        $this->container->expects($this->once())->method('get')->with(MercureOptions::class)->willReturn($options);

        $this->expectException(MercureException::class);
        $this->expectExceptionMessage(
            'You have to provide a non-empty secret key for the JWT generation, under mercure.jwt_secret',
        );

        ($this->factory)($this->container);
    }

    public static function provideInvalidConfigs(): iterable
    {
        yield 'empty config' => [new MercureOptions()];
        yield 'empty jwt secret' => [new MercureOptions(jwtSecret: '')];
    }

    #[Test]
    public function returnsExpectedObjectWhenProperConfigIsFound(): void
    {
        $secret = 'the _super_secure_secret';

        $this->container->expects($this->once())->method('get')->with(MercureOptions::class)->willReturn(
            new MercureOptions(jwtSecret: $secret),
        );

        $jwtConfig = ($this->factory)($this->container);

        self::assertInstanceOf(Sha256::class, $jwtConfig->signer());
        self::assertEquals($secret, $jwtConfig->signingKey()->contents());
    }
}
