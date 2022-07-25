<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Cache;

use Throwable;

interface RedisPublishingHelperInterface
{
    /**
     * @throws Throwable
     */
    public function publishPayloadInQueue(array $payload, string $queue): void;
}
