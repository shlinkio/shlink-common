<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger\Processor;

use Closure;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

use function array_filter;
use function in_array;

use const ARRAY_FILTER_USE_KEY;

class BackwardsCompatibleMonologProcessor implements ProcessorInterface
{
    private readonly Closure $legacyProcessor;

    /**
     * @param callable(array): array $legacyProcessor
     */
    public function __construct(callable $legacyProcessor)
    {
        $this->legacyProcessor = Closure::fromCallable($legacyProcessor);
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        // Clean-up unknown keys from the legacy processor
        $recordAsArray = array_filter(
            ($this->legacyProcessor)($record->toArray()),
            static fn (string $key) => in_array(
                $key,
                ['message', 'context', 'level', 'channel', 'datetime', 'extra'],
                strict: true,
            ),
            ARRAY_FILTER_USE_KEY,
        );
        if (isset($recordAsArray['level'])) {
            $recordAsArray['level'] = Level::tryFrom($recordAsArray['level']);
        }

        return $record->with(...$recordAsArray);
    }
}
