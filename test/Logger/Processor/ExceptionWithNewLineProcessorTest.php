<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger\Processor;

use Cake\Chronos\Chronos;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Logger\Processor\ExceptionWithNewLineProcessor;

use const PHP_EOL;

class ExceptionWithNewLineProcessorTest extends TestCase
{
    private ExceptionWithNewLineProcessor $processor;

    public function setUp(): void
    {
        $this->processor = new ExceptionWithNewLineProcessor();
    }

    #[Test]
    public function keepsRecordAsIsWhenNoPlaceholderExists(): void
    {
        $record = $this->createLogRecordWithMessage('Foo bar');
        self::assertSame($record, ($this->processor)($record));
    }

    #[Test, DataProvider('providePlaceholderRecords')]
    public function properlyReplacesExceptionPlaceholderAddingNewLine(string $message, string $expected): void
    {
        $record = $this->createLogRecordWithMessage($message);
        $result = ($this->processor)($record);

        self::assertNotSame($record, $result);
        self::assertEquals($expected, $result->message);
    }

    public static function providePlaceholderRecords(): iterable
    {
        yield ['Hello World with placeholder {e}', 'Hello World with placeholder ' . PHP_EOL . '{e}'];
        yield ['{e} Shlink', PHP_EOL . '{e} Shlink'];
        yield ['Foo {e} bar', 'Foo ' . PHP_EOL . '{e} bar'];
    }

    private function createLogRecordWithMessage(string $message): LogRecord
    {
        return new LogRecord(Chronos::now(), '', Level::Alert, $message);
    }
}
