<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use GuzzleHttp;

return [

    'http_client' => [
        'request_middlewares' => [],
        'response_middlewares' => [],
    ],

    'dependencies' => [
        'factories' => [
            GuzzleHttp\Client::class => Http\HttpClientFactory::class,
        ],
        'aliases' => [
            'httpClient' => GuzzleHttp\Client::class,
        ],
    ],

];
