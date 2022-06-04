<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

use function str_contains;
use function str_replace;

use const PHP_EOL;

final class ExceptionWithNewLineProcessor implements ProcessorInterface
{
    private const EXCEPTION_PLACEHOLDER = '{e}';

    public function __invoke(LogRecord $record): LogRecord
    {
        $message = $record->message;
        $messageHasExceptionPlaceholder = str_contains($message, self::EXCEPTION_PLACEHOLDER);

        if (! $messageHasExceptionPlaceholder) {
            return $record;
        }

        return $record->with(
            message: str_replace(self::EXCEPTION_PLACEHOLDER, PHP_EOL . self::EXCEPTION_PLACEHOLDER, $message),
        );
    }
}
