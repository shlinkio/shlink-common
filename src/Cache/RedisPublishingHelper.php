<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Cache;

use Predis\ClientInterface as PredisClient;
use Shlinkio\Shlink\Common\UpdatePublishing\PublishingHelperInterface;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;
use Throwable;

use function Shlinkio\Shlink\Json\json_encode;

readonly class RedisPublishingHelper implements PublishingHelperInterface
{
    public function __construct(private PredisClient $predis)
    {
    }

    /**
     * @throws Throwable
     */
    public function publishUpdate(Update $update): void
    {
        $this->predis->publish($update->topic, json_encode($update->payload));
    }
}
