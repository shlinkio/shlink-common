<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger;

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

use function Functional\map;

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

        $handler->setFormatter(self::buildLineFormatter($loggerConfig));

        return $handler;
    }

    private static function buildLineFormatter(array $loggerConfig): LineFormatter
    {
        $lineFormat = $loggerConfig['line_format'] ?? '';
        $addNewLine = $loggerConfig['add_new_line'] ?? true;
        if ($addNewLine) {
            $lineFormat = $lineFormat . PHP_EOL;
        }

        return new LineFormatter($lineFormat, null, true);
    }

    private static function resolveProcessors(array $loggerConfig, ContainerInterface $container): array
    {
        $extraProcessors = $loggerConfig['processors'] ?? [];
        return [
            new Processor\ExceptionWithNewLineProcessor(),
            new PsrLogMessageProcessor(),
            ...map($extraProcessors, static fn (string $value) => $container->get($value)),
        ];
    }
}
