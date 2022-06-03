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

use const PHP_EOL;

class LoggerFactory
{
    public static function __callStatic(string $name, array $arguments): LoggerInterface
    {
        /** @var ContainerInterface $container */
        [$container] = $arguments;
        $loggerConfig = $container->get('config')['logger'][$name] ?? null;

        if ($loggerConfig === null) {
            // TODO Throw error
        }

        return new Logger($name, [], LoggerFactory::resolveProcessors($loggerConfig, $container));
    }

    private static function buildHandler(array $loggerConfig): HandlerInterface
    {
        $type = LoggerType::tryFrom($loggerConfig['type'] ?? '');
        if ($type === null) {
            // TODO Throw error
        }

        $destination = $loggerConfig['destination'] ?? null;
        $level = Level::tryFrom($loggerConfig['level'] ?? '') ?? Level::Info;
        $handler = $type === LoggerType::FILE
            ? new RotatingFileHandler($destination ?? 'data/log/shlink_log.log', 30, $level, true, 0666)
            : new StreamHandler($destination ?? 'php://stdout', $level);

        $handler->setFormatter(new LineFormatter(
            '[%datetime%] [%extra.request_id%] %channel%.%level_name% - %message%' . PHP_EOL,
            null,
            true,
        ));

        return $handler;
    }

    private static function resolveProcessors(array $loggerConfig, ContainerInterface $container): array
    {
        $processors = [
            'exception_with_new_line' => new Processor\ExceptionWithNewLineProcessor(),
            'psr3' => new PsrLogMessageProcessor(),
        ];
        $extraProcessors = $loggerConfig['processors'] ?? [];

        foreach ($extraProcessors as $key => $value) {
            $processors[$key] = $container->get($value);
        }

        return $processors;
    }
}
