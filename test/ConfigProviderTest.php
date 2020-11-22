<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common;

use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    private ConfigProvider $configProvider;

    public function setUp(): void
    {
        $this->configProvider = new ConfigProvider();
    }

    /** @test */
    public function configIsReturned(): void
    {
        $config = $this->configProvider->__invoke();
        self::assertArrayHasKey('dependencies', $config);
    }
}
