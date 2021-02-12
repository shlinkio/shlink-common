<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function extension_loaded;

class ContentLengthMiddleware implements MiddlewareInterface
{
    private Closure $isSwoole;

    public function __construct(?callable $isSwoole = null)
    {
        $this->isSwoole = $isSwoole !== null
            ? Closure::fromCallable($isSwoole)
            : static fn () => extension_loaded('swoole');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);
        if (($this->isSwoole)() || $response->hasHeader('Content-Length')) {
            return $response;
        }

        $bodySize = $response->getBody()->getSize();
        if ($bodySize === null) {
            return $response;
        }

        return $response->withHeader('Content-Length', (string) $bodySize);
    }
}
