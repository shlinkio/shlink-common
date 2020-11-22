<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger\Processor;

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

    /** @test */
    public function keepsRecordAsIsWhenNoPlaceholderExists(): void
    {
        $record = ['message' => 'foobar2000'];
        self::assertSame($record, ($this->processor)($record));
    }

    /**
     * @test
     * @dataProvider providePlaceholderRecords
     */
    public function properlyReplacesExceptionPlaceholderAddingNewLine(array $record, array $expected): void
    {
        self::assertEquals($expected, ($this->processor)($record));
    }

    public function providePlaceholderRecords(): iterable
    {
        yield [
            ['message' => 'Hello World with placeholder {e}'],
            ['message' => 'Hello World with placeholder ' . PHP_EOL . '{e}'],
        ];
        yield [
            ['message' => '{e} Shlink'],
            ['message' => PHP_EOL . '{e} Shlink'],
        ];
        yield [
            ['message' => 'Foo {e} bar'],
            ['message' => 'Foo ' . PHP_EOL . '{e} bar'],
        ];
        yield [
            ['message' => 'Foo bar'],
            ['message' => 'Foo bar'],
        ];
    }
}
