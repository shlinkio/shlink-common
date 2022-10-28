<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Middleware;

use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Shlinkio\Shlink\Common\Middleware\ContentLengthMiddleware;

class ContentLengthMiddlewareTest extends TestCase
{
    private ContentLengthMiddleware $middleware;
    private bool $isSwoole;
    private MockObject & RequestHandlerInterface $handler;

    public function setUp(): void
    {
        $this->isSwoole = false;
        $this->middleware = new ContentLengthMiddleware(fn () => $this->isSwoole);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
    }

    /** @test */
    public function responseIsReturnedAsIsWhenIsSwoole(): void
    {
        $this->isSwoole = true;
        $respMock = $this->createMock(ResponseInterface::class);
        $respMock->expects($this->never())->method('hasHeader')->with('Content-Length');
        $respMock->expects($this->never())->method('getBody');
        $request = ServerRequestFactory::fromGlobals();
        $this->handler->expects($this->once())->method('handle')->with($request)->willReturn($respMock);

        $result = $this->middleware->process($request, $this->handler);

        self::assertSame($respMock, $result);
    }

    /** @test */
    public function responseIsReturnedAsIsWhenItAlreadyHasContentLength(): void
    {
        $respMock = $this->createMock(ResponseInterface::class);
        $respMock->expects($this->once())->method('hasHeader')->with('Content-Length')->willReturn(true);
        $respMock->expects($this->never())->method('getBody');
        $request = ServerRequestFactory::fromGlobals();
        $this->handler->expects($this->once())->method('handle')->with($request)->willReturn($respMock);

        $result = $this->middleware->process($request, $this->handler);

        self::assertSame($respMock, $result);
    }

    /** @test */
    public function responseIsReturnedAsIsWhenBodySizeIsNull(): void
    {
        $respMock = $this->createMock(ResponseInterface::class);
        $respMock->expects($this->once())->method('hasHeader')->with('Content-Length')->willReturn(false);
        $respMock->expects($this->never())->method('withHeader');
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())->method('getSize')->willReturn(null);
        $respMock->expects($this->once())->method('getBody')->willReturn($body);
        $request = ServerRequestFactory::fromGlobals();
        $this->handler->expects($this->once())->method('handle')->with($request)->willReturn($respMock);

        $result = $this->middleware->process($request, $this->handler);

        self::assertSame($respMock, $result);
    }

    /** @test */
    public function responseIsReturnedWithNewHeaderWhenBodySizeIsNotNull(): void
    {
        $respMock = $this->createMock(ResponseInterface::class);
        $respMock->expects($this->once())->method('hasHeader')->with('Content-Length')->willReturn(false);
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())->method('getSize')->willReturn(100);
        $respMock->expects($this->once())->method('getBody')->willReturn($body);
        $respMock->expects($this->once())->method('withHeader')->with('Content-Length', '100')->willReturn($respMock);
        $request = ServerRequestFactory::fromGlobals();
        $this->handler->expects($this->once())->method('handle')->with($request)->willReturn($respMock);

        $result = $this->middleware->process($request, $this->handler);

        self::assertSame($respMock, $result);
    }
}
