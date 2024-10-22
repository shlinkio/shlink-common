<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Response;

use Endroid\QrCode\Builder\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Response\QrCodeResponse;

class QrCodeResponseTest extends TestCase
{
    #[Test]
    public function providedQrCodeIsSetAsBody(): void
    {
        $qrCode = (new Builder(data: 'Hello'))->build();
        $resp = new QrCodeResponse($qrCode);

        self::assertEquals($qrCode->getMimeType(), $resp->getHeaderLine('Content-Type'));
        self::assertEquals($qrCode->getString(), (string) $resp->getBody());
    }
}
