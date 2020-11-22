<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Shlinkio\Shlink\Common\Exception\MercureException;
use Shlinkio\Shlink\Common\Mercure\LcobucciJwtProvider;
use Shlinkio\Shlink\Common\Mercure\PublisherFactory;

class PublisherFactoryTest extends TestCase
{
    use ProphecyTrait;

    private PublisherFactory $factory;
    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new PublisherFactory();
    }

    /**
     * @test
     * @dataProvider provideInvalidConfigs
     */
    public function throwsExceptionWhenNoHubUrlIsConfigured(array $config): void
    {
        $getConfig = $this->container->get('config')->willReturn($config);
        $getJwtProvider = $this->container->get(LcobucciJwtProvider::class)->willReturn(function (): void {
        });

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
        $getJwtProvider = $this->container->get(LcobucciJwtProvider::class)->willReturn(function (): void {
        });

        $publisher = ($this->factory)($this->container->reveal());

        $ref = new ReflectionObject($publisher);
        $prop = $ref->getProperty('hubUrl');
        $prop->setAccessible(true);

        self::assertEquals($expectedHubUrl . '/.well-known/mercure', $prop->getValue($publisher));
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
