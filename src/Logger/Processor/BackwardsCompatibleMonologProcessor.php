<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger\Processor;

use Closure;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

use function Functional\select_keys;

class BackwardsCompatibleMonologProcessor implements ProcessorInterface
{
    /** @var callable(array): array */
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
        $recordAsArray = select_keys(
            ($this->legacyProcessor)($record->toArray()),
            ['message', 'context', 'level', 'channel', 'datetime', 'extra'],
        );
        if ($recordAsArray['level']) {
            $recordAsArray['level'] = Level::tryFrom($recordAsArray['level']);
        }

        return $record->with(...$recordAsArray);
    }
}
