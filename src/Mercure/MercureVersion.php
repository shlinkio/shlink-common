<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Mercure;

enum MercureVersion: string
{
    case LEGACY = 'legacy';
    case STABLE = 'stable';
}
