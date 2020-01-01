<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Rest;

interface DataTransformerInterface
{
    /**
     * @param mixed $value
     */
    public function transform($value): array;
}
