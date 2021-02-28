<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Response;

use Endroid\QrCode\Writer\Result\ResultInterface;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\StreamInterface;

class QrCodeResponse extends Response
{
    use Response\InjectContentTypeTrait;

    public function __construct(ResultInterface $qrCode, int $status = StatusCode::STATUS_OK, array $headers = [])
    {
        parent::__construct(
            $this->createBody($qrCode),
            $status,
            $this->injectContentType($qrCode->getMimeType(), $headers),
        );
    }

    private function createBody(ResultInterface $qrCode): StreamInterface
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($qrCode->getString());
        $body->rewind();
        return $body;
    }
}
