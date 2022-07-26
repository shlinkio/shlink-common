<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\UpdatePublishing;

use Throwable;

interface PublishingHelperInterface
{
    /**
     * @throws Throwable
     */
    public function publishUpdate(Update $update): void;
}
