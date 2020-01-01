<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger;

use Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\Common\Logger\ErrorLogger;

class ErrorLoggerTest extends TestCase
{
    private ErrorLogger $errorLogger;
    private ObjectProphecy $logger;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->errorLogger = new ErrorLogger($this->logger->reveal());
    }

    /** @test */
    public function delegatesIntoInternalLoggerWhenInvoked(): void
    {
        $e = new Exception('Something wrong');
        $error = $this->logger->error((string) $e)->will(function () {
        });

        ($this->errorLogger)($e);

        $error->shouldHaveBeenCalledOnce();
    }
}
