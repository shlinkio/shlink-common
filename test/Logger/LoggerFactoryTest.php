<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Logger\Exception\InvalidLoggerException;
use Shlinkio\Shlink\Common\Logger\LoggerFactory;
use Shlinkio\Shlink\Common\Logger\LoggerType;

class LoggerFactoryTest extends TestCase
{
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    #[Test]
    public function anExceptionIsThrownWhenRequestedLoggerDoesNotHaveConfig(): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn(['logger' => []]);

        $this->expectException(InvalidLoggerException::class);
        $this->expectExceptionMessage(
            'Provided logger with name "foo" is not valid. Make sure to provide a value defined under the "logger" '
            . 'config key.',
        );

        LoggerFactory::foo($this->container); // @phpstan-ignore-line
    }

    #[Test, DataProvider('provideConfigWithInvalidType')]
    public function anExceptionIsThrownWhenConfiguredTypeIsInvalid(array $config): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'logger' => ['foo' => $config],
        ]);

        $this->expectException(InvalidLoggerException::class);
        $this->expectExceptionMessage('Expected one of ["file", "stream"]');

        LoggerFactory::foo($this->container); // @phpstan-ignore-line
    }

    public static function provideConfigWithInvalidType(): iterable
    {
        yield 'no type' => [[]];
        yield 'invalid type' => [['type' => 'invalid']];
    }

    /**
     * @param class-string<ProcessableHandlerInterface> $expectedHandler
     */
    #[Test, DataProvider('provideTypes')]
    public function expectedHandlerIsCreated(array $config, string $expectedHandler, callable $assertConfig): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'logger' => ['foo' => $config],
        ]);

        /** @var Logger $logger */
        $logger = LoggerFactory::foo($this->container); // @phpstan-ignore-line
        $handlers = $logger->getHandlers();

        self::assertCount(1, $handlers);
        self::assertInstanceOf($expectedHandler, $handlers[0]);
        self::assertInstanceOf(FormattableHandlerInterface::class, $handlers[0]);
        self::assertInstanceOf(LineFormatter::class, $handlers[0]->getFormatter());
        $assertConfig($handlers[0]);
    }

    public static function provideTypes(): iterable
    {
        yield [
            ['type' => LoggerType::FILE->value],
            RotatingFileHandler::class,
            static function (RotatingFileHandler $handler): void {
                $url = $handler->getUrl();

                Assert::assertTrue($handler->getBubble());
                Assert::assertNotNull($url);
                Assert::assertStringContainsString('data/log/shlink_log', $url);
            },
        ];
        yield [
            ['type' => LoggerType::FILE->value, 'destination' => 'foobar'],
            RotatingFileHandler::class,
            static function (RotatingFileHandler $handler): void {
                $url = $handler->getUrl();

                Assert::assertTrue($handler->getBubble());
                Assert::assertNotNull($url);
                Assert::assertStringContainsString('foobar', $url);
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

    #[Test, DataProvider('provideExtraProcessors')]
    public function extraProcessorsAreAdded(array $config, int $expectedAmountOfProcessors): void
    {
        $id = static fn (mixed $v) => $v;
        $this->container->expects($this->exactly($expectedAmountOfProcessors + 1))->method('get')->willReturnCallback(
            fn (string $serviceName) => $serviceName !== 'config' ? $id : ['logger' => [
                'foo' => ['type' => LoggerType::STREAM->value, ...$config],
            ]],
        );

        /** @var Logger $logger */
        $logger = LoggerFactory::foo($this->container); // @phpstan-ignore-line
        $processors = $logger->getProcessors();

        self::assertCount($expectedAmountOfProcessors + 2, $processors);
    }

    public static function provideExtraProcessors(): iterable
    {
        yield [[], 0];
        yield [['processors' => []], 0];
        yield [['processors' => ['one']], 1];
        yield [['processors' => ['one', 'two', 'three']], 3];
    }

    #[Test, DataProvider('provideLevelConfig')]
    public function expectedLevelIsSetBasedOnConfig(array $config, Level $expectedLevel): void
    {
        $this->container->method('get')->willReturn(['logger' => [
            'bar' => ['type' => LoggerType::STREAM->value, ...$config],
        ]]);

        /** @var Logger $logger */
        $logger = LoggerFactory::bar($this->container); // @phpstan-ignore-line
        $handlers = $logger->getHandlers();

        self::assertNotEmpty($handlers);
        self::assertInstanceOf(AbstractHandler::class, $handlers[0]);
        self::assertEquals($expectedLevel, $handlers[0]->getLevel());
    }

    public static function provideLevelConfig(): iterable
    {
        yield 'no config' => [[], Level::Info];
        yield 'invalid level' => [['level' => 30000], Level::Info];
        yield 'valid level' => [['level' => Level::Debug->value], Level::Debug];
        yield 'another valid level' => [['level' => Level::Emergency->value], Level::Emergency];
    }

    /**
     * @param class-string<FormatterInterface> $expectedFormatter
     */
    #[Test]
    #[TestWith([[
        'formatter' => ['type' => 'json'],
    ], JsonFormatter::class], 'explicit JSON formatter')]
    #[TestWith([[
        'formatter' => ['type' => 'console'],
    ], LineFormatter::class], 'explicit console formatter')]
    #[TestWith([['formatter' => []], LineFormatter::class], 'default with "formatter" config')]
    #[TestWith([[], LineFormatter::class], 'default without "formatter" config')]
    public function expectedFormatterIsCreated(array $config, string $expectedFormatter): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'logger' => [
                'foo' => ['type' => LoggerType::STREAM->value, ...$config],
            ],
        ]);

        /** @var Logger $logger */
        $logger = LoggerFactory::foo($this->container); // @phpstan-ignore-line
        $formatter = $logger->getHandlers()[0]->getFormatter(); // @phpstan-ignore-line

        self::assertInstanceOf($expectedFormatter, $formatter);
    }

    #[Test]
    public function exceptionIsThrownIfInvalidFormatterIsConfigured(): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn([
            'logger' => [
                'foo' => [
                    'type' => LoggerType::STREAM->value,
                    'formatter' => ['type' => 'invalid'],
                ],
            ],
        ]);

        $this->expectException(InvalidLoggerException::class);
        $this->expectExceptionMessage(
            'Provided formatter type "invalid" is not valid. Expected one of ["console", "json"]',
        );

        LoggerFactory::foo($this->container); // @phpstan-ignore-line
    }
}
