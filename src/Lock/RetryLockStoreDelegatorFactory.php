<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Lock;

use Interop\Container\ContainerInterface;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\RetryTillSaveStore;

class RetryLockStoreDelegatorFactory
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): RetryTillSaveStore
    {
        /** @var PersistingStoreInterface $originalStore */
        $originalStore = $callback();
        return new RetryTillSaveStore($originalStore);
    }
}
