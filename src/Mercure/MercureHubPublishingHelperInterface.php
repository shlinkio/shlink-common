<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use Shlinkio\Shlink\Common\UpdatePublishing\PublishingHelperInterface;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;
use Symfony\Component\Mercure\Hub;
use Symfony\Component\Mercure\Update as MercureUpdate;
use Throwable;

use function Shlinkio\Shlink\Common\json_encode;

class MercureHubPublishingHelper implements PublishingHelperInterface
{
    public function __construct(private readonly Hub $mercure)
    {
    }

    /**
     * @throws Throwable
     */
    public function publishUpdate(Update $update): void
    {
        $this->mercure->publish(new MercureUpdate($update->topic, json_encode($update->payload)));
    }
}
