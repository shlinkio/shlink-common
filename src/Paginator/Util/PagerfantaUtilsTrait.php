<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Paginator\Util;

use Pagerfanta\Pagerfanta;
use Shlinkio\Shlink\Common\Rest\DataTransformerInterface;

/**
 * @deprecated Use PagerfantaUtils instead
 * @phpstan-ignore trait.unused
 */
trait PagerfantaUtilsTrait
{
    /**
     * @template T
     * @param Pagerfanta<T> $paginator
     */
    private function serializePaginator(
        Pagerfanta $paginator,
        DataTransformerInterface|null $transformer = null,
        string $dataProp = 'data',
    ): array {
        $serializer = $transformer !== null ? fn ($value) => $transformer->transform($value) : null;
        return PagerfantaUtils::serializePaginator($paginator, $serializer, $dataProp);
    }

    /**
     * @template T
     * @param Pagerfanta<T> $paginator
     */
    private function formatCurrentPageMessage(Pagerfanta $paginator, string $pattern): string
    {
        return PagerfantaUtils::formatCurrentPageMessage($paginator, $pattern);
    }
}
