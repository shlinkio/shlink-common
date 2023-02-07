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
    public function throwsExceptionWhenProperConfigCouldNotBeFound(array $config): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $this->expectException(MercureException::class);
        $this->expectExceptionMessage(
            'You have to provide a non-empty secret key for the JWT generation, under mercure.jwt_secret',
        );

        ($this->factory)($this->container);
    }

    public static function provideInvalidConfigs(): iterable
    {
        yield 'empty config' => [[]];
        yield 'empty mercure config' => [['mercure' => []]];
        yield 'null jwt secret' => [['mercure' => [
            'jwt_secret' => null,
        ]]];
        yield 'empty jwt secret' => [['mercure' => [
            'jwt_secret' => '',
        ]]];
        yield 'non-string' => [['mercure' => [
            'jwt_secret' => ['foo', 'bar'],
        ]]];
    }

    #[Test]
    public function returnsExpectedObjectWhenProperConfigIsFound(): void
    {
        $secret = 'the _super_secure_secret';

        $this->container->expects($this->once())->method('get')->with('config')->willReturn(['mercure' => [
            'jwt_secret' => $secret,
        ]]);

        $jwtConfig = ($this->factory)($this->container);

        self::assertInstanceOf(Sha256::class, $jwtConfig->signer());
        self::assertEquals($secret, $jwtConfig->signingKey()->contents());
    }
}
