<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use function Functional\some;
use function sprintf;
use function str_ends_with;

class AccessLogMiddleware implements MiddlewareInterface
{
    public const LOGGER_SERVICE_NAME = self::class . '/Logger';

    public function __construct(private readonly LoggerInterface $logger, private readonly array $ignoreList = [])
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $path = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();

        // Do not log requests for ignored endpoints
        if (some($this->ignoreList, fn (string $ignored) => str_ends_with($path, $ignored))) {
            return $response;
        }

        $this->logger->info(sprintf(
            '%s %s %s %s',
            $request->getMethod(),
            $this->requestUri($path, $query),
            $response->getStatusCode(),
            $response->getHeaderLine('Content-Length'),
        ));

        return $response;
    }

    private function requestUri(string $path, string $query): string
    {
        return $query === '' ? $path : sprintf('%s?%s', $path, $query);
    }
}
