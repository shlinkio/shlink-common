<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger;

use Psr\Container\ContainerInterface;
use Zend\Stratigility\Middleware\ErrorHandler;

class ErrorHandlerListenerAttachingDelegator
{
    public function __invoke(ContainerInterface $container, $name, callable $callback): ErrorHandler
    {
        /** @var ErrorHandler $instance */
        $instance = $callback();
        $listeners = $container->get('config')['error_handler']['listeners'] ?? [];
        foreach ($listeners as $listener) {
            $instance->attachListener($container->get($listener));
        }

        return $instance;
    }
}
