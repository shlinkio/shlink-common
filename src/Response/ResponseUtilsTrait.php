<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Response;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use finfo;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Stdlib\ArrayUtils;
use Psr\Http\Message\ResponseInterface;

use const FILEINFO_MIME;

trait ResponseUtilsTrait
{
    private function generateBinaryResponse(string $path, array $extraHeaders = []): ResponseInterface
    {
        $body = new Stream($path);
        return new Response($body, StatusCode::STATUS_OK, ArrayUtils::merge([
            'Content-Type' => (new finfo(FILEINFO_MIME))->file($path),
            'Content-Length' => (string) $body->getSize(),
        ], $extraHeaders));
    }
}
