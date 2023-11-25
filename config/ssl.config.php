<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

return [

    // SSL options used for secure connections with RabbitMQ and Redis
    'ssl_options' => [
        // Allow self-signed certificates
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ],

];
