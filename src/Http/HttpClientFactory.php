<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Http;

use GuzzleHttp;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Http\Exception\InvalidHttpMiddlewareException;

use function is_callable;
use function is_string;

class HttpClientFactory
{
    public function __invoke(ContainerInterface $container): GuzzleHttp\Client
    {
        $handler = GuzzleHttp\HandlerStack::create();
        [$requestMiddlewares, $responseMiddlewares] = $this->resolveMiddlewares($container);

        foreach ($requestMiddlewares as $middleware) {
            $middlewareInstance = is_string($middleware) ? $container->get($middleware) : $middleware;
            if (! is_callable($middlewareInstance)) {
                throw InvalidHttpMiddlewareException::fromRequestMiddleware($middlewareInstance);
            }

            $handler->push(GuzzleHttp\Middleware::mapRequest($middlewareInstance));
        }

        foreach ($responseMiddlewares as $middleware) {
            $middlewareInstance = is_string($middleware) ? $container->get($middleware) : $middleware;
            if (! is_callable($middlewareInstance)) {
                throw InvalidHttpMiddlewareException::fromResponseMiddleware($middlewareInstance);
            }

            $handler->push(GuzzleHttp\Middleware::mapResponse($middlewareInstance));
        }

        return new GuzzleHttp\Client(['handler' => $handler]);
    }

    private function resolveMiddlewares(ContainerInterface $container): array
    {
        $config = $container->get('config')['http_client'] ?? [];
        return [
            $config['request_middlewares'] ?? [],
            $config['response_middlewares'] ?? [],
        ];
    }
}
