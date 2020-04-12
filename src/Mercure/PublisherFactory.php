<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Exception\MercureException;
use Symfony\Component\Mercure\Publisher;

use function sprintf;

class PublisherFactory
{
    public function __invoke(ContainerInterface $container): Publisher
    {
        $mercureConfig = $container->get('config')['mercure'] ?? [];
        $mercureHub = $mercureConfig['internal_hub_url'] ?? $mercureConfig['public_hub_url'] ?? null;
        if ($mercureHub === null) {
            throw MercureException::missingHubUrl();
        }

        $jwtProvider = $container->get(LcobucciJwtProvider::class);

        return new Publisher(sprintf('%s/.well-known/mercure', $mercureHub), $jwtProvider);
    }
}
