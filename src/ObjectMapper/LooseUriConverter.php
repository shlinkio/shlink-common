<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\ObjectMapper;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

use function preg_match;

#[AsConverter]
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class LooseUriConverter
{
    private const string LOOSE_URI_MATCHER = '/(.+)\:(.+)/i'; // Matches anything starting with a schema

    public function map(string $value): string
    {
        if (preg_match(self::LOOSE_URI_MATCHER, $value) !== 1) {
            throw MappingError::withBody('Provided value does not match a loose URI pattern');
        }

        return $value;
    }
}
