<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\ObjectMapper;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

use function array_map;
use function array_unique;
use function mb_strtolower;
use function preg_replace;
use function strip_tags;
use function trim;

/**
 * Converts a list of tags, applying:
 * 1. Walk all entries and:
 *   a) Strip tags and trim
 *   b) Convert to lowercase
 *   c) Replace spaces with dashes
 * 2. Remove duplicates
 */
#[AsConverter]
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class TagsConverter
{
    /**
     * @param string[] $value
     * @return string[]
     */
    public function map(array $value): array
    {
        // @phpstan-ignore return.type
        return array_unique(
            array_map(fn (string $tag) => preg_replace('/ /', '-', mb_strtolower(trim(strip_tags($tag)))), $value),
        );
    }
}
