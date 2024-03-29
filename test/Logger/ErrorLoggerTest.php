<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger;

use Exception;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Shlinkio\Shlink\Common\Logger\ErrorLogger;

class ErrorLoggerTest extends TestCase
{
    private ErrorLogger $errorLogger;
    private MockObject & LoggerInterface $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->errorLogger = new ErrorLogger($this->logger);
    }

    #[Test, DataProvider('provideStatusCodes')]
    public function delegatesIntoInternalLoggerWhenInvoked(int $status, string $expectedLevel): void
    {
        $e = new Exception('Something wrong');
        $this->logger->expects($this->once())->method('log')->with($expectedLevel, (string) $e);

        ($this->errorLogger)($e, new ServerRequest(), (new Response())->withStatus($status));
    }

    public static function provideStatusCodes(): iterable
    {
        yield 'status 500' => [500, LogLevel::ERROR];
        yield 'status 503' => [503, LogLevel::ERROR];
        yield 'status 504' => [504, LogLevel::ERROR];
        yield 'status 400' => [400, LogLevel::DEBUG];
        yield 'status 401' => [401, LogLevel::DEBUG];
        yield 'status 403' => [403, LogLevel::DEBUG];
        yield 'status 404' => [404, LogLevel::DEBUG];
        yield 'status 499' => [499, LogLevel::DEBUG];
        yield 'status 200' => [200, LogLevel::DEBUG];
        yield 'status 301' => [301, LogLevel::DEBUG];
    }
}
