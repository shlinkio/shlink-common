<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\ObjectMapper;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

use function array_map;
use function mb_strtolower;
use function preg_replace;
use function strip_tags;
use function trim;

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
        return array_map(fn (string $tag) => preg_replace('/ /', '-', mb_strtolower(trim(strip_tags($tag)))), $value);
    }
}
