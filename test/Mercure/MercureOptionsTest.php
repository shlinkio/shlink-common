<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Mercure\MercureOptions;

class MercureOptionsTest extends TestCase
{
    #[Test]
    #[TestWith([true])]
    #[TestWith([false])]
    public function isEnabledOnlyWHenPublicUrlExists(bool $isEnabled): void
    {
        $options = new MercureOptions(enabled: $isEnabled);

        self::assertEquals($options->enabled, $isEnabled);
        self::assertEquals($options->isEnabled(), $isEnabled);
    }
}
