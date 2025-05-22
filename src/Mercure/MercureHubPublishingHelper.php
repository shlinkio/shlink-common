<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use Shlinkio\Shlink\Common\UpdatePublishing\PublishingHelperInterface;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update as MercureUpdate;
use Throwable;

use function Shlinkio\Shlink\Json\json_encode;

readonly class MercureHubPublishingHelper implements PublishingHelperInterface
{
    public function __construct(private HubInterface $mercure)
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
