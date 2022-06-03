<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Logger\Exception\InvalidLoggerException;
use Shlinkio\Shlink\Common\Logger\LoggerFactory;

class LoggerFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /** @test */
    public function anExceptionIsThrownWhenRequestedLoggerDoesNotHaveConfig(): void
    {
        $getConfig = $this->container->get('config')->willReturn(['logger' => []]);

        $getConfig->shouldBeCalledOnce();
        $this->expectException(InvalidLoggerException::class);
        $this->expectExceptionMessage(
            'Provided logger with name "foo" is not valid. Make sure to provide a value defined under the "logger" '
            . 'config key.',
        );

        LoggerFactory::foo($this->container->reveal());
    }

    /**
     * @test
     * @dataProvider provideConfigWithInvalidType
     */
    public function anExceptionIsThrownWhenConfiguredTypeIsInvalid(array $config): void
    {
        $getConfig = $this->container->get('config')->willReturn([
            'logger' => ['foo' => $config],
        ]);

        $getConfig->shouldBeCalledOnce();
        $this->expectException(InvalidLoggerException::class);
        $this->expectExceptionMessage('Expected one of ["file", "stream"]');

        LoggerFactory::foo($this->container->reveal());
    }

    public function provideConfigWithInvalidType(): iterable
    {
        yield 'no type' => [[]];
        yield 'invalid type' => [['type' => 'invalid']];
    }

    public function fileHandlerIsCreatedWhenConfigured(): void
    {
    }

    public function streamHandlerIsCreatedWhenConfigured(): void
    {
    }

    public function extraProcessorsAreAdded(): void
    {
    }
}
