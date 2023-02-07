<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Middleware;

use Doctrine\DBAL\Connection;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Shlinkio\Shlink\Common\Doctrine\ReopeningEntityManagerInterface;
use Shlinkio\Shlink\Common\Middleware\CloseDbConnectionMiddleware;

class CloseDbConnectionMiddlewareTest extends TestCase
{
    private CloseDbConnectionMiddleware $middleware;
    private MockObject & RequestHandlerInterface $handler;
    private MockObject & ReopeningEntityManagerInterface $em;
    private MockObject & Connection $conn;

    public function setUp(): void
    {
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->em = $this->createMock(ReopeningEntityManagerInterface::class);
        $this->conn = $this->createMock(Connection::class);

        $this->middleware = new CloseDbConnectionMiddleware($this->em);
    }

    #[Test]
    public function connectionIsClosedWhenMiddlewareIsProcessed(): void
    {
        $req = new ServerRequest();
        $resp = new Response();
        $this->handler->expects($this->once())->method('handle')->with($req)->willReturn($resp);
        $this->conn->expects($this->once())->method('close');
        $this->em->expects($this->once())->method('getConnection')->willReturn($this->conn);
        $this->em->expects($this->once())->method('open');
        $this->em->expects($this->once())->method('close');

        $result = $this->middleware->process($req, $this->handler);

        self::assertSame($result, $resp);
    }

    #[Test]
    public function connectionIsClosedEvenIfExceptionIsThrownOnInnerMiddlewares(): void
    {
        $req = new ServerRequest();
        $expectedError = new RuntimeException();
        $this->handler->expects($this->once())->method('handle')->with($req)->willThrowException($expectedError);
        $this->conn->expects($this->once())->method('close');
        $this->em->expects($this->once())->method('getConnection')->willReturn($this->conn);
        $this->em->expects($this->once())->method('open');
        $this->em->expects($this->once())->method('close');

        $this->expectExceptionObject($expectedError);

        $this->middleware->process($req, $this->handler);
    }
}
