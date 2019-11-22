<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger;

use Psr\Container\ContainerInterface;
use Psr\Log;

class LoggerAwareDelegatorFactory
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback)
    {
        $instance = $callback();
        if ($instance instanceof Log\LoggerAwareInterface && $container->has(Log\LoggerInterface::class)) {
            $instance->setLogger($container->get(Log\LoggerInterface::class));
        }

        return $instance;
    }
}
