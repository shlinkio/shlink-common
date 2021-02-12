<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Middleware;

use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Shlinkio\Shlink\Common\Middleware\ContentLengthMiddleware;

class ContentLengthMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    private ContentLengthMiddleware $middleware;
    private bool $isSwoole;
    private ObjectProphecy $handler;

    public function setUp(): void
    {
        $this->isSwoole = false;
        $this->middleware = new ContentLengthMiddleware(fn () => $this->isSwoole);
        $this->handler = $this->prophesize(RequestHandlerInterface::class);
    }

    /** @test */
    public function responseIsReturnedAsIsWhenIsSwoole(): void
    {
        $this->isSwoole = true;
        $resp = $this->prophesize(ResponseInterface::class);
        $respMock = $resp->reveal();
        $request = ServerRequestFactory::fromGlobals();
        $handle = $this->handler->handle($request)->willReturn($respMock);

        $result = $this->middleware->process($request, $this->handler->reveal());

        self::assertSame($respMock, $result);
        $handle->shouldHaveBeenCalledOnce();
        $resp->hasHeader('Content-Length')->shouldNotHaveBeenCalled();
        $resp->getBody()->shouldNotHaveBeenCalled();
    }

    /** @test */
    public function responseIsReturnedAsIsWhenItAlreadyHasContentLength(): void
    {
        $resp = $this->prophesize(ResponseInterface::class);
        $hasHeader = $resp->hasHeader('Content-Length')->willReturn(true);
        $respMock = $resp->reveal();
        $request = ServerRequestFactory::fromGlobals();
        $handle = $this->handler->handle($request)->willReturn($respMock);

        $result = $this->middleware->process($request, $this->handler->reveal());

        self::assertSame($respMock, $result);
        $handle->shouldHaveBeenCalledOnce();
        $hasHeader->shouldHaveBeenCalledOnce();
        $resp->getBody()->shouldNotHaveBeenCalled();
    }

    /** @test */
    public function responseIsReturnedAsIsWhenBodySizeIsNull(): void
    {
        $resp = $this->prophesize(ResponseInterface::class);
        $hasHeader = $resp->hasHeader('Content-Length')->willReturn(false);
        $body = $this->prophesize(StreamInterface::class);
        $getSize = $body->getSize()->willReturn(null);
        $getBody = $resp->getBody()->willReturn($body->reveal());
        $respMock = $resp->reveal();
        $request = ServerRequestFactory::fromGlobals();
        $handle = $this->handler->handle($request)->willReturn($respMock);

        $result = $this->middleware->process($request, $this->handler->reveal());

        self::assertSame($respMock, $result);
        $handle->shouldHaveBeenCalledOnce();
        $hasHeader->shouldHaveBeenCalledOnce();
        $getSize->shouldHaveBeenCalledOnce();
        $getBody->shouldHaveBeenCalledOnce();
        $resp->withHeader(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    /** @test */
    public function responseIsReturnedWithNewHeaderWhenBodySizeIsNotNull(): void
    {
        $resp = $this->prophesize(ResponseInterface::class);
        $hasHeader = $resp->hasHeader('Content-Length')->willReturn(false);
        $body = $this->prophesize(StreamInterface::class);
        $getSize = $body->getSize()->willReturn(100);
        $getBody = $resp->getBody()->willReturn($body->reveal());
        $respMock = $resp->reveal();
        $withHeader = $resp->withHeader('Content-Length', '100')->willReturn($respMock);
        $request = ServerRequestFactory::fromGlobals();
        $handle = $this->handler->handle($request)->willReturn($respMock);

        $result = $this->middleware->process($request, $this->handler->reveal());

        self::assertSame($respMock, $result);
        $handle->shouldHaveBeenCalledOnce();
        $hasHeader->shouldHaveBeenCalledOnce();
        $getSize->shouldHaveBeenCalledOnce();
        $getBody->shouldHaveBeenCalledOnce();
        $withHeader->shouldHaveBeenCalledOnce();
    }
}
