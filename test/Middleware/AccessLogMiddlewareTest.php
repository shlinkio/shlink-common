<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\Common\Middleware\AccessLogMiddleware;

class AccessLogMiddlewareTest extends TestCase
{
    private AccessLogMiddleware $middleware;
    private MockObject & LoggerInterface $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->middleware = new AccessLogMiddleware($this->logger);
    }

    #[Test, DataProvider('provideMessages')]
    public function basicRequestAndResponseInfoIsLogged(
        ServerRequestInterface $req,
        ResponseInterface $resp,
        string $expectedMessage,
    ): void {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($req)->willReturn($resp);

        $this->logger->expects($this->once())->method('info')->with($expectedMessage);

        $this->middleware->process($req, $handler);
    }

    public static function provideMessages(): iterable
    {
        yield [
            ServerRequestFactory::fromGlobals()->withMethod('GET')->withUri(new Uri('/foo/bar')),
            new Response(),
            'GET /foo/bar 200 ',
        ];
        yield [
            ServerRequestFactory::fromGlobals()->withMethod('PATCH')->withUri(new Uri('/something')),
            new Response('php://memory', 400, ['Content-Length' => 300]),
            'PATCH /something 400 300',
        ];
        yield [
            ServerRequestFactory::fromGlobals()->withMethod('DELETE')->withUri(new Uri('/foo')),
            new Response('php://memory', 204, ['Content-Length' => 0]),
            'DELETE /foo 204 0',
        ];
    }
}
