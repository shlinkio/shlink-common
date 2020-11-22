<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Response;

use Endroid\QrCode\QrCode;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Response\QrCodeResponse;

class QrCodeResponseTest extends TestCase
{
    /** @test */
    public function providedQrCodeIsSetAsBody(): void
    {
        $qrCode = new QrCode('Hello');
        $resp = new QrCodeResponse($qrCode);

        self::assertEquals($qrCode->getContentType(), $resp->getHeaderLine('Content-Type'));
        self::assertEquals($qrCode->writeString(), (string) $resp->getBody());
    }
}
