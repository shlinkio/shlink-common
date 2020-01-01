<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Log;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\Common\Logger\LoggerAwareDelegatorFactory;
use stdClass;

class LoggerAwareDelegatorFactoryTest extends TestCase
{
    private LoggerAwareDelegatorFactory $delegator;
    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->delegator = new LoggerAwareDelegatorFactory();
    }

    /**
     * @test
     * @dataProvider provideInstances
     */
    public function injectsLoggerOnInstanceWhenImplementingLoggerAware(
        object $instance,
        bool $hasLogger,
        int $expectedHasLoggerCalls,
        int $expectedGetLoggerCalls
    ): void {
        $callback = fn () => $instance;
        $getLogger = $this->container->get(Log\LoggerInterface::class)->willReturn(new Log\NullLogger());
        $hasLogger = $this->container->has(Log\LoggerInterface::class)->willReturn($hasLogger);

        $result = ($this->delegator)($this->container->reveal(), '', $callback);

        $this->assertSame($instance, $result);
        $hasLogger->shouldHaveBeenCalledTimes($expectedHasLoggerCalls);
        $getLogger->shouldHaveBeenCalledTimes($expectedGetLoggerCalls);
    }

    public function provideInstances(): iterable
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
