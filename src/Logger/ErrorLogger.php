<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger;

use Psr\Log\LoggerInterface;
use Throwable;

class ErrorLogger
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Throwable $error): void
    {
        $this->logger->error((string) $error);
    }
}
