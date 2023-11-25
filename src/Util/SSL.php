<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Util;

final class SSL
{
    // SSL options used for secure connections with RabbitMQ and Redis
    // https://www.php.net/manual/en/context.ssl.php
    public const OPTIONS = [
        // Allow self-signed certificates
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ];
}
