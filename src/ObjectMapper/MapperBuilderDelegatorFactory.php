<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\ObjectMapper;

use Cake\Chronos\Chronos;
use CuyZ\Valinor\MapperBuilder;
use DateTimeInterface;
use Psr\Container\ContainerInterface;

use function Shlinkio\Shlink\Common\normalizeDate;
use function strip_tags;
use function trim;

class MapperBuilderDelegatorFactory
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): MapperBuilder
    {
        /** @var MapperBuilder $mapperBuilder */
        $mapperBuilder = $callback();
        return $mapperBuilder

            // Format dates
            ->supportDateFormats(DateTimeInterface::ATOM, 'Y-m-d')
            ->infer(DateTimeInterface::class, fn () => Chronos::class)
            ->registerConstructor(fn (string $date): Chronos => normalizeDate($date))

            // Trim and strip tags on strings
            ->registerConverter(
                fn (string $value, callable $next): string => $next(trim(strip_tags($value))),
                priority: -9999, // This should always be called first
            );
    }
}
