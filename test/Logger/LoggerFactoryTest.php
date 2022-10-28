<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Logger\Exception\InvalidLoggerException;
use Shlinkio\Shlink\Common\Logger\LoggerFactory;
use Shlinkio\Shlink\Common\Logger\LoggerType;

use function Functional\id;

class LoggerFactoryTest extends TestCase
{
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /** @test */
    public function anExceptionIsThrownWhenRequestedLoggerDoesNotHaveConfig(): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn(['logger' => []]);

        $this->expectException(InvalidLoggerException::class);
        $this->expectExceptionMessage(
            'Provided logger with name "foo" is not valid. Make sure to provide a value defined under the "logger" '
            . 'config key.',
        );

        LoggerFactory::foo($this->container);
    }

    /**
     * @test
     * @dataProvider provideConfigWithInvalidType
     */
    public function anExceptionIsThrownWhenConfiguredTypeIsInvalid(array $config): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'logger' => ['foo' => $config],
        ]);

        $this->expectException(InvalidLoggerException::class);
        $this->expectExceptionMessage('Expected one of ["file", "stream"]');

        LoggerFactory::foo($this->container);
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
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'logger' => ['foo' => $config],
        ]);

        /** @var Logger $logger */
        $logger = LoggerFactory::foo($this->container);
        $handlers = $logger->getHandlers();

        self::assertCount(1, $handlers);
        self::assertInstanceOf($expectedHandler, $handlers[0]);
        self::assertInstanceOf(LineFormatter::class, $handlers[0]->getFormatter());
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
        $this->container->expects($this->exactly($expectedAmountOfProcessors + 1))->method('get')->willReturnCallback(
            fn (string $serviceName) => $serviceName !== 'config' ? id(...) : ['logger' => [
                'foo' => ['type' => LoggerType::STREAM->value, ...$config],
            ]],
        );

        /** @var Logger $logger */
        $logger = LoggerFactory::foo($this->container);
        $processors = $logger->getProcessors();

        self::assertCount($expectedAmountOfProcessors + 2, $processors);
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
        $this->container->method('get')->with('config')->willReturn(['logger' => [
            'bar' => ['type' => LoggerType::STREAM->value, ...$config],
        ]]);

        /** @var Logger $logger */
        $logger = LoggerFactory::bar($this->container);

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
