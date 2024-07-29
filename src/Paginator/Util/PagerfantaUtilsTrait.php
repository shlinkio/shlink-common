<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Paginator\Util;

use Pagerfanta\Pagerfanta;
use Shlinkio\Shlink\Common\Rest\DataTransformerInterface;

/**
 * @deprecated Use PagerfantaUtils instead
 */
trait PagerfantaUtilsTrait
{
    /**
     * @template T
     * @param Pagerfanta<T> $paginator
     */
    private function serializePaginator(
        Pagerfanta $paginator,
        ?DataTransformerInterface $transformer = null,
        string $dataProp = 'data',
    ): array {
        return PagerfantaUtils::serializePaginator($paginator, $transformer, $dataProp);
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
