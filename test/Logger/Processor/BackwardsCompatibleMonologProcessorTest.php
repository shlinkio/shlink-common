<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Logger\Processor;

use Cake\Chronos\Chronos;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Logger\Processor\BackwardsCompatibleMonologProcessor;

class BackwardsCompatibleMonologProcessorTest extends TestCase
{
    private BackwardsCompatibleMonologProcessor $processor;

    public function setUp(): void
    {
        $this->processor = new BackwardsCompatibleMonologProcessor(static fn (array $v) => $v);
    }

    #[Test]
    public function argumentAndResultAreParsed(): void
    {
        $record = new LogRecord(Chronos::now(), '', Level::Info, 'The log message');
        $result = ($this->processor)($record);

        self::assertEquals($record, $result);
    }
}
