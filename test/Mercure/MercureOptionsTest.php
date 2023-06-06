<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Mercure\MercureOptions;

class MercureOptionsTest extends TestCase
{
    #[Test, DataProvider('providePublicHubUrls')]
    public function isEnabledOnlyWHenPublicUrlExists(?string $publicHubUrl, bool $expectedIsEnabled): void
    {
        $options = new MercureOptions(publicHubUrl: $publicHubUrl);
        self::assertEquals($options->isEnabled(), $expectedIsEnabled);
    }

    public static function providePublicHubUrls(): iterable
    {
        yield 'null public hub URL' => [null, false];
        yield 'empty public hub URL' => ['', false];
        yield 'pseudo-empty public hub URL' => ['   ', false];
        yield 'non-empty public hub URL' => ['foo', true];
    }
}
