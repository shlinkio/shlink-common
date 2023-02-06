<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger;

use Laminas\Stratigility\Middleware\ErrorHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Logger\ErrorHandlerListenerAttachingDelegator;

class ErrorHandlerListenerAttachingDelegatorTest extends TestCase
{
    private ErrorHandlerListenerAttachingDelegator $delegator;
    private MockObject & ContainerInterface $container;
    private MockObject & ErrorHandler $errorHandler;

    public function setUp(): void
    {
        $this->errorHandler = $this->createMock(ErrorHandler::class);
        $this->container = $this->createMock(ContainerInterface::class);
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
        $this->container->expects($this->exactly($expectedCalls + 1))->method('get')->willReturnCallback(
            fn (string $serviceName) => $serviceName === 'config' ? $config : $listener,
        );
        $this->errorHandler->expects($this->exactly($expectedCalls))->method('attachListener')->with($listener);
        $callbackInvoked = false;

        $expected = $this->errorHandler;
        $result = ($this->delegator)($this->container, '', function () use (&$callbackInvoked, $expected) {
            $callbackInvoked = true;
            return $expected;
        });

        self::assertSame($expected, $result);
        self::assertTrue($callbackInvoked);
    }

    public static function provideConfig(): iterable
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
