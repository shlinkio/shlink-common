<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\Common\Logger\Exception\InvalidLoggerException;

use function array_map;
use function in_array;

use const PHP_EOL;

class LoggerFactory
{
    public static function __callStatic(string $name, array $arguments): LoggerInterface
    {
        /** @var ContainerInterface $container */
        [$container] = $arguments;
        $loggerConfig = $container->get('config')['logger'][$name] ?? null;

        if ($loggerConfig === null) {
            throw InvalidLoggerException::fromInvalidName($name);
        }

        return new Logger(
            $name,
            [self::buildHandler($loggerConfig)],
            self::resolveProcessors($loggerConfig, $container),
        );
    }

    private static function buildHandler(array $loggerConfig): HandlerInterface
    {
        $configuredType = $loggerConfig['type'] ?? '';
        $type = LoggerType::tryFrom($configuredType);
        if ($type === null) {
            throw InvalidLoggerException::fromInvalidType($configuredType);
        }

        $destination = $loggerConfig['destination'] ?? null;
        $level = Level::tryFrom($loggerConfig['level'] ?? Level::Info->value) ?? Level::Info;
        $handler = $type === LoggerType::FILE
            ? new RotatingFileHandler($destination ?? 'data/log/shlink_log.log', 30, $level, true, 0666)
            : new StreamHandler($destination ?? 'php://stdout', $level);

        $handler->setFormatter(self::buildFormatter($loggerConfig));

        return $handler;
    }

    private static function buildFormatter(array $loggerConfig): FormatterInterface
    {
        $formatterType = $loggerConfig['formatter']['type'] ?? 'console';
        if (! in_array($formatterType, ['console', 'json'], strict: true)) {
            throw InvalidLoggerException::fromInvalidFormatterType((string) $formatterType);
        }

        $formatterConfig = $loggerConfig['formatter'] ?? $loggerConfig;
        $addNewLine = $formatterConfig['add_new_line'] ?? true;

        if ($formatterType === 'json') {
            return new JsonFormatter(appendNewline: $addNewLine);
        }

        $lineFormat = $formatterConfig['line_format'] ?? '';
        if ($addNewLine) {
            $lineFormat = $lineFormat . PHP_EOL;
        }

        return new LineFormatter($lineFormat, allowInlineLineBreaks: true);
    }

    private static function resolveProcessors(array $loggerConfig, ContainerInterface $container): array
    {
        $extraProcessors = $loggerConfig['processors'] ?? [];
        return [
            new Processor\ExceptionWithNewLineProcessor(),
            new PsrLogMessageProcessor(),
            ...array_map(static fn (string $value) => $container->get($value), $extraProcessors),
        ];
    }
}
