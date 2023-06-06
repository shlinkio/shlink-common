<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Exception\MercureException;
use Symfony\Component\Mercure\Hub;

use function sprintf;

class HubFactory
{
    public function __invoke(ContainerInterface $container): Hub
    {
        $mercureOptions = $container->get(MercureOptions::class);
        $mercureHub = $mercureOptions->internalHubUrl ?? $mercureOptions->publicHubUrl ?? null;
        if ($mercureHub === null) {
            throw MercureException::missingHubUrl();
        }

        $jwtProvider = $container->get(LcobucciJwtProvider::class);

        return new Hub(sprintf('%s/.well-known/mercure', $mercureHub), $jwtProvider);
    }
}
