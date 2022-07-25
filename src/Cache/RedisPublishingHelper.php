<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Cache;

use Predis\ClientInterface as PredisClient;
use Throwable;

use function Shlinkio\Shlink\Common\json_encode;

class RedisPublishingHelper implements RedisPublishingHelperInterface
{
    public function __construct(private readonly PredisClient $predis)
    {
    }

    /**
     * @throws Throwable
     */
    public function publishPayloadInQueue(array $payload, string $queue): void
    {
        $this->predis->publish($queue, json_encode($payload));
    }
}
