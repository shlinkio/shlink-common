<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\Common\Logger\LoggerAwareDelegatorFactory;
use stdClass;

class LoggerAwareDelegatorFactoryTest extends TestCase
{
    private LoggerAwareDelegatorFactory $delegator;
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->delegator = new LoggerAwareDelegatorFactory();
    }

    #[Test, DataProvider('provideInstances')]
    public function injectsLoggerOnInstanceWhenImplementingLoggerAware(
        object $instance,
        bool $hasLogger,
        int $expectedHasLoggerCalls,
        int $expectedGetLoggerCalls,
    ): void {
        $callback = fn () => $instance;
        $this->container->expects($this->exactly($expectedHasLoggerCalls))->method('has')->with(
            Log\LoggerInterface::class,
        )->willReturn($hasLogger);
        $this->container->expects($this->exactly($expectedGetLoggerCalls))->method('get')->with(
            Log\LoggerInterface::class,
        )->willReturn(new Log\NullLogger());

        $result = ($this->delegator)($this->container, '', $callback);

        self::assertSame($instance, $result);
    }

    public static function provideInstances(): iterable
    {
        $loggerAware = new class implements Log\LoggerAwareInterface {
            public function setLogger(LoggerInterface $logger): void
            {
                Assert::assertInstanceOf(Log\NullLogger::class, $logger);
            }
        };

        yield 'no logger aware and no logger registered' => [new stdClass(), false, 0, 0];
        yield 'no logger aware and logger registered' => [new stdClass(), true, 0, 0];
        yield 'logger aware and no logger registered' => [$loggerAware, false, 1, 0];
        yield 'logger aware and logger registered' => [$loggerAware, true, 1, 1];
    }
}
