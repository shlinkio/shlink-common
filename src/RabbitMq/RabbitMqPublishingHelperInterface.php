<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\RabbitMq;

use Throwable;

interface RabbitMqPublishingHelperInterface
{
    /**
     * @throws Throwable
     */
    public function publishPayloadInQueue(array $payload, string $queue): void;
}
