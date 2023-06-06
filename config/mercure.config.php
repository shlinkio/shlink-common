<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Lcobucci\JWT\Configuration;
use Shlinkio\Shlink\Config\Factory\ValinorConfigFactory;
use Symfony\Component\Mercure\Hub;

return [

    'mercure' => [],

    'dependencies' => [
        'factories' => [
            Configuration::class => Mercure\JwtConfigFactory::class,
            Mercure\LcobucciJwtProvider::class => ConfigAbstractFactory::class,
            Hub::class => Mercure\HubFactory::class,
            Mercure\MercureHubPublishingHelper::class => ConfigAbstractFactory::class,
            Mercure\MercureOptions::class => [ValinorConfigFactory::class, 'config.mercure'],
        ],
    ],

    ConfigAbstractFactory::class => [
        Mercure\LcobucciJwtProvider::class => [Configuration::class, Mercure\MercureOptions::class],
        Mercure\MercureHubPublishingHelper::class => [Hub::class],
    ],

];
