<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use Lcobucci\JWT\Signer\Hmac\Sha256;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Exception\MercureException;
use Shlinkio\Shlink\Common\Mercure\JwtConfigFactory;

class JwtConfigFactoryTest extends TestCase
{
    use ProphecyTrait;

    private JwtConfigFactory $factory;
    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->factory = new JwtConfigFactory();
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @test
     * @dataProvider provideInvalidConfigs
     */
    public function throwsExceptionWhenProperConfigCouldNotBeFound(array $config): void
    {
        $getConfig = $this->container->get('config')->willReturn($config);

        $this->expectException(MercureException::class);
        $this->expectExceptionMessage(
            'You have to provide a secret key for the JWT generation, under mercure.jwt_secret',
        );
        $getConfig->shouldBeCalledOnce();

        ($this->factory)($this->container->reveal());
    }

    public function provideInvalidConfigs(): iterable
    {
        yield 'empty config' => [[]];
        yield 'empty mercure config' => [['mercure' => []]];
        yield 'empty jwt secret' => [['mercure' => [
            'jwt_secret' => null,
        ]]];
    }

    /** @test */
    public function returnsExpectedObjectWhenProperConfigIsFound(): void
    {
        $secret = 'the _super_secure_secret';

        $getConfig = $this->container->get('config')->willReturn(['mercure' => [
            'jwt_secret' => $secret,
        ]]);

        $jwtConfig = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(Sha256::class, $jwtConfig->getSigner());
        $this->assertEquals($secret, $jwtConfig->getSigningKey()->getContent());
        $getConfig->shouldHaveBeenCalledOnce();
    }
}
