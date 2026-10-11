<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Mercure\MercureOptions;
use Shlinkio\Shlink\Common\Mercure\MercureVersion;

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

    #[Test]
    #[TestWith([new MercureOptions(version: MercureVersion::LEGACY), 'mercureAuthorization'])]
    #[TestWith([new MercureOptions(version: MercureVersion::STABLE), '__Secure-mercure_access_token'])]
    #[TestWith([new MercureOptions(authCookieName: 'mercure_access_token'), 'mercure_access_token'])]
    public function expectedCookieNameIsSet(MercureOptions $options, string $expectedCookie): void
    {
        self::assertEquals($options->authCookieName, $expectedCookie);
    }
}
