<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger\Processor;

use Psr\Container\ContainerInterface;

class BackwardsCompatibleMonologProcessorDelegator
{
    public function __invoke(
        ContainerInterface $container,
        string $name,
        callable $callback,
    ): BackwardsCompatibleMonologProcessor {
        return new BackwardsCompatibleMonologProcessor($callback());
    }
}
