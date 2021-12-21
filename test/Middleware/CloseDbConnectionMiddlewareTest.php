<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Middleware;

use Doctrine\DBAL\Connection;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Shlinkio\Shlink\Common\Doctrine\ReopeningEntityManagerInterface;
use Shlinkio\Shlink\Common\Middleware\CloseDbConnectionMiddleware;

class CloseDbConnectionMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    private CloseDbConnectionMiddleware $middleware;
    private ObjectProphecy $handler;
    private ObjectProphecy $em;
    private ObjectProphecy $conn;

    public function setUp(): void
    {
        $this->handler = $this->prophesize(RequestHandlerInterface::class);
        $this->em = $this->prophesize(ReopeningEntityManagerInterface::class);
        $this->conn = $this->prophesize(Connection::class);
        $this->conn->close()->will(function (): void {
        });
        $this->em->getConnection()->willReturn($this->conn->reveal());
        $this->em->close()->will(function (): void {
        });
        $this->em->open()->will(function (): void {
        });

        $this->middleware = new CloseDbConnectionMiddleware($this->em->reveal());
    }

    /** @test */
    public function connectionIsClosedWhenMiddlewareIsProcessed(): void
    {
        $req = new ServerRequest();
        $resp = new Response();
        $handle = $this->handler->handle($req)->willReturn($resp);

        $result = $this->middleware->process($req, $this->handler->reveal());

        self::assertSame($result, $resp);
        $this->em->open()->shouldHaveBeenCalledOnce();
        $this->em->getConnection()->shouldHaveBeenCalledOnce();
        $this->conn->close()->shouldHaveBeenCalledOnce();
        $this->em->close()->shouldHaveBeenCalledOnce();
        $handle->shouldHaveBeenCalledOnce();
    }

    /** @test */
    public function connectionIsClosedEvenIfExceptionIsThrownOnInnerMiddlewares(): void
    {
        $req = new ServerRequest();
        $expectedError = new RuntimeException();
        $this->handler->handle($req)->willThrow($expectedError)
                                    ->shouldBeCalledOnce();

        $this->em->open()->shouldBeCalledOnce();
        $this->em->getConnection()->shouldBeCalledOnce();
        $this->conn->close()->shouldBeCalledOnce();
        $this->em->close()->shouldBeCalledOnce();
        $this->expectExceptionObject($expectedError);

        $this->middleware->process($req, $this->handler->reveal());
    }
}
