<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Shlinkio\Shlink\Common\Exception\MercureException;
use Shlinkio\Shlink\Common\Mercure\HubFactory;
use Shlinkio\Shlink\Common\Mercure\LcobucciJwtProvider;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

class HubFactoryTest extends TestCase
{
    private HubFactory $factory;
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = new HubFactory();
    }

    #[Test, DataProvider('provideInvalidConfigs')]
    public function throwsExceptionWhenNoHubUrlIsConfigured(array $config): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $this->expectException(MercureException::class);
        $this->expectExceptionMessage(
            'You have to provide mercure hub URL under mercure.internal_hub_url or mercure.public_hub_url',
        );

        ($this->factory)($this->container);
    }

    public static function provideInvalidConfigs(): iterable
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

    #[Test, DataProvider('provideValidConfigs')]
    public function returnsExpectedObjectIfProperConfigIsFound(array $config, string $expectedHubUrl): void
    {
        $this->container->expects($this->exactly(2))->method('get')->willReturnMap([
            ['config', $config],
            [LcobucciJwtProvider::class, $this->createMock(TokenProviderInterface::class)],
        ]);

        $hub = ($this->factory)($this->container);

        $ref = new ReflectionObject($hub);
        $prop = $ref->getProperty('url');
        $prop->setAccessible(true);

        self::assertEquals($expectedHubUrl . '/.well-known/mercure', $prop->getValue($hub));
    }

    public static function provideValidConfigs(): iterable
    {
        yield 'with internal url' => [['mercure' => [
            'internal_hub_url' => $url = 'http://foo.com',
        ]], $url];
        yield 'with public url' => [['mercure' => [
            'public_hub_url' => $url = 'http://bar.com',
        ]], $url];
    }
}
