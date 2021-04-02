<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Shlinkio\Shlink\Common\Exception\MercureException;
use Shlinkio\Shlink\Common\Mercure\HubFactory;
use Shlinkio\Shlink\Common\Mercure\LcobucciJwtProvider;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

class HubFactoryTest extends TestCase
{
    use ProphecyTrait;

    private HubFactory $factory;
    private ObjectProphecy $container;
    private ObjectProphecy $tokenProvider;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->tokenProvider = $this->prophesize(TokenProviderInterface::class);
        $this->factory = new HubFactory();
    }

    /**
     * @test
     * @dataProvider provideInvalidConfigs
     */
    public function throwsExceptionWhenNoHubUrlIsConfigured(array $config): void
    {
        $getConfig = $this->container->get('config')->willReturn($config);
        $getJwtProvider = $this->container->get(LcobucciJwtProvider::class)->willReturn($this->tokenProvider->reveal());

        $this->expectException(MercureException::class);
        $this->expectExceptionMessage(
            'You have to provide mercure hub URL under mercure.internal_hub_url or mercure.public_hub_url',
        );
        $getConfig->shouldBeCalledOnce();
        $getJwtProvider->shouldNotBeCalled();

        ($this->factory)($this->container->reveal());
    }

    public function provideInvalidConfigs(): iterable
    {
        yield 'empty config' => [[]];
        yield 'empty mercure' => [['mercure' => []]];
        yield 'empty public url' => [['mercure' => [
            'public_hub_url' => null,
        ]]];
        yield 'empty internal url' => [['mercure' => [
            'internal_hub_url' => null,
        ]]];
        yield 'both urls empty' => [['mercure' => [
            'internal_hub_url' => null,
            'public_hub_url' => null,
        ]]];
    }

    /**
     * @test
     * @dataProvider provideValidConfigs
     */
    public function returnsExpectedObjectIfProperConfigIsFound(array $config, string $expectedHubUrl): void
    {
        $getConfig = $this->container->get('config')->willReturn($config);
        $getJwtProvider = $this->container->get(LcobucciJwtProvider::class)->willReturn($this->tokenProvider->reveal());

        $hub = ($this->factory)($this->container->reveal());

        $ref = new ReflectionObject($hub);
        $prop = $ref->getProperty('url');
        $prop->setAccessible(true);

        self::assertEquals($expectedHubUrl . '/.well-known/mercure', $prop->getValue($hub));
        $getConfig->shouldHaveBeenCalledOnce();
        $getJwtProvider->shouldHaveBeenCalledOnce();
    }

    public function provideValidConfigs(): iterable
    {
        yield 'with internal url' => [['mercure' => [
            'internal_hub_url' => $url = 'http://foo.com',
        ]], $url];
        yield 'with public url' => [['mercure' => [
            'public_hub_url' => $url = 'http://bar.com',
        ]], $url];
    }
}
