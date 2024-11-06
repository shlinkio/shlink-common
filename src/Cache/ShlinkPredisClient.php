<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Cache;

use Predis\Client;
use Predis\ClientInterface;
use Predis\Connection\Replication\ReplicationInterface;

use function class_alias;

// This file is a fix for https://github.com/shlinkio/shlink/issues/1684, until symfony/cache supports predis 2
// phpcs:disable
class_alias(ReplicationInterface::class, 'Predis\Connection\Aggregate\ReplicationInterface');

class ShlinkPredisClient extends Client
{
    public function getClientFor(string $name): ClientInterface
    {
        return parent::getClientBy('role', $name);
    }
}
