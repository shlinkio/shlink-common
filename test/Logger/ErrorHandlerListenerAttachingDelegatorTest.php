<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger;

use Laminas\Stratigility\Middleware\ErrorHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Logger\ErrorHandlerListenerAttachingDelegator;

class ErrorHandlerListenerAttachingDelegatorTest extends TestCase
{
    private ErrorHandlerListenerAttachingDelegator $delegator;
    private ObjectProphecy $container;
    private ObjectProphecy $errorHandler;

    public function setUp(): void
    {
        $this->errorHandler = $this->prophesize(ErrorHandler::class);
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->delegator = new ErrorHandlerListenerAttachingDelegator();
    }

    /**
     * @test
     * @dataProvider provideConfig
     */
    public function attachesAllRegisteredListeners(int $expectedCalls, array $config): void
    {
        $listener = function (): void {
        };
        $getConfig = $this->container->get('config')->willReturn($config);
        $getListener = $this->container->get(Argument::not('config'))->willReturn($listener);
        $attachListener = $this->errorHandler->attachListener($listener)->will(function (): void {
        });
        $callbackInvoked = false;

        $expected = $this->errorHandler->reveal();
        $result = ($this->delegator)($this->container->reveal(), '', function () use (&$callbackInvoked, $expected) {
            $callbackInvoked = true;
            return $expected;
        });

        $this->assertSame($expected, $result);
        $this->assertTrue($callbackInvoked);
        $getConfig->shouldHaveBeenCalledOnce();
        $getListener->shouldHaveBeenCalledTimes($expectedCalls);
        $attachListener->shouldHaveBeenCalledTimes($expectedCalls);
    }

    public function provideConfig(): iterable
    {
        yield [0, []];
        yield [0, ['error_handler' => []]];
        yield [0, ['error_handler' => ['listeners' => []]]];
        yield [3, ['error_handler' => ['listeners' => [
            'foo',
            'bar',
            'baz',
        ]]]];
    }
}
