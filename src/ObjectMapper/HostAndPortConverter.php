<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\ObjectMapper;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use Respect\Validation\Validator as v;

use function array_pad;
use function count;
use function explode;

#[AsConverter]
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class HostAndPortConverter
{
    /**
     * @param callable(mixed): string $next
     */
    public function map(string $value, callable $next): string
    {
        $parts = explode(':', $value);
        if (count($parts) > 2) {
            throw new MappingError('Provided value, once split using the ":" separator, returned more than 2 parts');
        }

        [$host, $port] = array_pad($parts, length: 2, value: null);
        if (! $this->hostIsValid($host)) {
            throw new MappingError('The host part of the value is not valid. It must be a hostname or IP address');
        }

        if (! $this->portIsValid($port)) {
            throw new MappingError('The port part of the value is not valid. It must be a number between 1 and 65535');
        }

        return $next($value);
    }

    private function hostIsValid(string|null $host): bool
    {
        if ($host === null) {
            return false;
        }

        return $host === 'localhost' || v::ip()->validate($host) || v::domain(tldCheck: false)->validate($host);
    }

    private function portIsValid(string|null $port): bool
    {
        if ($port === null) {
            return true;
        }

        $portAsNumber = (int) $port;
        return ($port === (string) $portAsNumber) && $portAsNumber > 0 && $portAsNumber <= 65_535;
    }
}
