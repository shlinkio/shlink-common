<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Middleware;

use DateTimeImmutable;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Shlinkio\Shlink\Common\Middleware\RequestIdMiddleware;

class RequestIdMiddlewareTest extends TestCase
{
    private RequestIdMiddleware $middleware;
    private MockObject & RequestHandlerInterface $handler;

    public function setUp(): void
    {
        $this->middleware = new RequestIdMiddleware();

        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->handler->method('handle')->willReturn(new Response());
    }

    #[Test]
    public function providedRequestIdIsRespected(): void
    {
        $request = ServerRequestFactory::fromGlobals()->withHeader('X-Request-Id', 'foobar');
        $response = $this->middleware->process($request, $this->handler);

        self::assertEquals('foobar', $response->getHeaderLine('X-Request-Id'));
    }

    #[Test]
    public function newRequestIdsAreGeneratedPerRequest(): void
    {
        $request = ServerRequestFactory::fromGlobals();
        $response1 = $this->middleware->process($request, $this->handler);
        $response2 = $this->middleware->process($request, $this->handler);
        $response3 = $this->middleware->process($request, $this->handler);

        self::assertNotEquals($response1->getHeaderLine('X-Request-Id'), $response2->getHeaderLine('X-Request-Id'));
        self::assertNotEquals($response1->getHeaderLine('X-Request-Id'), $response3->getHeaderLine('X-Request-Id'));
        self::assertNotEquals($response2->getHeaderLine('X-Request-Id'), $response3->getHeaderLine('X-Request-Id'));
    }

    #[Test]
    public function currentRequestIdCanBeRead(): void
    {
        self::assertEquals('-', $this->middleware->currentRequestId());
        $request = ServerRequestFactory::fromGlobals();
        $this->middleware->process($request, $this->handler);
        self::assertNotEquals('-', $this->middleware->currentRequestId());
    }

    #[Test]
    public function currentRequestIdCanBeSet(): void
    {
        self::assertEquals('-', $this->middleware->currentRequestId());
        $this->middleware->setCurrentRequestId('foobar');
        self::assertEquals('foobar', $this->middleware->currentRequestId());
    }

    #[Test]
    public function defaultRequestIdIsSetForLogs(): void
    {
        $result = ($this->middleware)($this->defaultLogRecord());
        self::assertEquals('-', $result->extra[RequestIdMiddleware::ATTRIBUTE]);
    }

    #[Test]
    public function expectedRequestIdIsSetForLogs(): void
    {
        // Trigger a request before trying to parse the log record
        $request = ServerRequestFactory::fromGlobals();
        $response = $this->middleware->process($request, $this->handler);

        $result = ($this->middleware)($this->defaultLogRecord());

        self::assertEquals($response->getHeaderLine('X-Request-Id'), $result->extra[RequestIdMiddleware::ATTRIBUTE]);
    }

    private function defaultLogRecord(): LogRecord
    {
        return new LogRecord(datetime: new DateTimeImmutable(), channel: '', level: Level::Critical, message: '');
    }
}
