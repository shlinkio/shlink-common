<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Logger\Exception\InvalidLoggerException;
use Shlinkio\Shlink\Common\Logger\LoggerFactory;
use Shlinkio\Shlink\Common\Logger\LoggerType;

use function Functional\id;

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

    /**
     * @test
     * @dataProvider provideTypes
     */
    public function expectedHandlerIsCreated(array $config, string $expectedHandler, callable $assertConfig): void
    {
        $getConfig = $this->container->get('config')->willReturn([
            'logger' => ['foo' => $config],
        ]);

        /** @var Logger $logger */
        $logger = LoggerFactory::foo($this->container->reveal());
        $handlers = $logger->getHandlers();

        self::assertCount(1, $handlers);
        self::assertInstanceOf($expectedHandler, $handlers[0]);
        self::assertInstanceOf(LineFormatter::class, $handlers[0]->getFormatter());
        $getConfig->shouldHaveBeenCalledOnce();
        $assertConfig($handlers[0]);
    }

    public function provideTypes(): iterable
    {
        yield [
            ['type' => LoggerType::FILE->value],
            RotatingFileHandler::class,
            static function (RotatingFileHandler $handler): void {
                Assert::assertTrue($handler->getBubble());
                Assert::assertStringContainsString('data/log/shlink_log', $handler->getUrl());
            },
        ];
        yield [
            ['type' => LoggerType::FILE->value, 'destination' => 'foobar'],
            RotatingFileHandler::class,
            static function (RotatingFileHandler $handler): void {
                Assert::assertTrue($handler->getBubble());
                Assert::assertStringContainsString('foobar', $handler->getUrl());
            },
        ];
        yield [
            ['type' => LoggerType::STREAM->value],
            StreamHandler::class,
            static function (StreamHandler $handler): void {
                Assert::assertEquals('php://stdout', $handler->getUrl());
            },
        ];
        yield [
            ['type' => LoggerType::STREAM->value, 'destination' => 'php://memory'],
            StreamHandler::class,
            static function (StreamHandler $handler): void {
                Assert::assertEquals('php://memory', $handler->getUrl());
            },
        ];
    }

    /**
     * @test
     * @dataProvider provideExtraProcessors
     */
    public function extraProcessorsAreAdded(array $config, int $expectedAmountOfProcessors): void
    {
        $getConfig = $this->container->get('config')->willReturn(['logger' => [
            'foo' => ['type' => LoggerType::STREAM->value, ...$config],
        ]]);
        $getProcessor = $this->container->get(
            Argument::that(static fn (string $arg) => $arg !== 'config'),
        )->willReturn(id(...));

        /** @var Logger $logger */
        $logger = LoggerFactory::foo($this->container->reveal());
        $processors = $logger->getProcessors();

        self::assertCount($expectedAmountOfProcessors + 2, $processors);
        $getConfig->shouldHaveBeenCalledOnce();
        $getProcessor->shouldHaveBeenCalledTimes($expectedAmountOfProcessors);
    }

    public function provideExtraProcessors(): iterable
    {
        yield [[], 0];
        yield [['processors' => []], 0];
        yield [['processors' => ['one']], 1];
        yield [['processors' => ['one', 'two', 'three']], 3];
    }

    /**
     * @test
     * @dataProvider provideLevelConfig
     */
    public function expectedLevelIsSetBasedOnConfig(array $config, Level $expectedLevel): void
    {
        $this->container->get('config')->willReturn(['logger' => [
            'bar' => ['type' => LoggerType::STREAM->value, ...$config],
        ]]);

        /** @var Logger $logger */
        $logger = LoggerFactory::bar($this->container->reveal());

        self::assertEquals($expectedLevel, $logger->getHandlers()[0]->getLevel());
    }

    public function provideLevelConfig(): iterable
    {
        yield 'no config' => [[], Level::Info];
        yield 'invalid level' => [['level' => 30000], Level::Info];
        yield 'valid level' => [['level' => Level::Debug->value], Level::Debug];
        yield 'another valid level' => [['level' => Level::Emergency->value], Level::Emergency];
    }
}
