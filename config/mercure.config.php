<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Lcobucci\JWT\Configuration;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mercure\Hub;

use function trim;

return [

    'mercure' => [],

    'dependencies' => [
        'factories' => [
            Configuration::class => Mercure\JwtConfigFactory::class,
            Mercure\LcobucciJwtProvider::class => ConfigAbstractFactory::class,
            Hub::class => Mercure\HubFactory::class,
            Mercure\MercureHubPublishingHelper::class => ConfigAbstractFactory::class,
            Mercure\MercureOptions::class => static function (ContainerInterface $c): Mercure\MercureOptions {
                $config = $c->get('config.mercure');
                return new Mercure\MercureOptions(
                    enabled: $config['enabled']
                        // Fallback to the previous mechanism to check if mercure was enabled, if the "enabled" key is
                        // not present.
                        // This fallback is deprecated.
                        ?? (isset($config['public_hub_url']) && trim($config['public_hub_url']) !== ''),
                    publicHubUrl: $config['public_hub_url'] ?? null,
                    internalHubUrl: $config['internal_hub_url'] ?? $config['public_hub_url'] ?? null,
                    jwtSecret: $config['jwt_secret'] ?? null,
                    jwtIssuer: $config['jwt_issuer'] ?? 'Shlink',
                );
            },
        ],
    ],

    ConfigAbstractFactory::class => [
        Mercure\LcobucciJwtProvider::class => [Configuration::class, Mercure\MercureOptions::class],
        Mercure\MercureHubPublishingHelper::class => [Hub::class],
    ],

];
