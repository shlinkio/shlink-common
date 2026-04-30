<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\ObjectMapper;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

use function substr;
use function trim;

#[AsConverter]
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final readonly class SubstringConverter
{
    /**
     * @param positive-int $length
     */
    public function __construct(private int $length)
    {
    }

    public function map(string $value): string
    {
        return substr(trim($value), offset: 0, length: $this->length);
    }
}
