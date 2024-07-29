<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Paginator\Util;

use Laminas\Stdlib\ArrayUtils;
use Pagerfanta\Pagerfanta;
use Shlinkio\Shlink\Common\Rest\DataTransformerInterface;

use function array_map;
use function count;
use function sprintf;

/**
 * @template T
 */
final class PagerfantaUtils
{
    /**
     * @param Pagerfanta<T> $paginator
     */
    public static function serializePaginator(
        Pagerfanta $paginator,
        ?DataTransformerInterface $transformer = null,
        string $dataProp = 'data',
    ): array {
        $currentPageItems = ArrayUtils::iteratorToArray($paginator->getCurrentPageResults());

        return [
            $dataProp => self::serializeItems($currentPageItems, $transformer),
            'pagination' => [
                'currentPage' => $paginator->getCurrentPage(),
                'pagesCount' => $paginator->getNbPages(),
                'itemsPerPage' => $paginator->getMaxPerPage(),
                'itemsInCurrentPage' => count($currentPageItems),
                'totalItems' => $paginator->getNbResults(),
            ],
        ];
    }

    private static function serializeItems(array $items, ?DataTransformerInterface $transformer = null): array
    {
        return $transformer === null ? $items : array_map([$transformer, 'transform'], $items);
    }

    /**
     * @param Pagerfanta<T> $paginator
     */
    public static function formatCurrentPageMessage(Pagerfanta $paginator, string $pattern): string
    {
        return sprintf($pattern, $paginator->getCurrentPage(), $paginator->getNbPages());
    }
}
