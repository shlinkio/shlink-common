<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log;
use Throwable;

class ErrorLogger
{
    public function __construct(private Log\LoggerInterface $logger)
    {
    }

    public function __invoke(Throwable $error, ServerRequestInterface $request, ResponseInterface $response): void
    {
        $status = $response->getStatusCode();
        $level = $this->determineLogLevel($status);

        $this->logger->log($level, (string) $error);
    }

    private function determineLogLevel(int $status): string
    {
        return $status >= 500 ? Log\LogLevel::ERROR : Log\LogLevel::DEBUG;
    }
}
