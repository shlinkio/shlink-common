<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\ProblemDetails\ProblemDetailsMiddleware;
use Psr\Container\ContainerInterface;

class ErrorHandlerListenerAttachingDelegator
{
    public function __invoke(
        ContainerInterface $container,
        string $name,
        callable $callback,
    ): ErrorHandler|ProblemDetailsMiddleware {
        /** @var ErrorHandler|ProblemDetailsMiddleware $instance */
        $instance = $callback();
        $listeners = $container->get('config')['error_handler']['listeners'] ?? [];
        foreach ($listeners as $listener) {
            $instance->attachListener($container->get($listener));
        }

        return $instance;
    }
}
