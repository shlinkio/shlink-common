<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Paginator\Util;

use Laminas\Stdlib\ArrayUtils;
use Pagerfanta\Pagerfanta;

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
     * @param null|callable(T): mixed $serializer
     */
    public static function serializePaginator(
        Pagerfanta $paginator,
        callable|null $serializer = null,
        string $dataProp = 'data',
    ): array {
        $currentPageItems = ArrayUtils::iteratorToArray($paginator->getCurrentPageResults());

        return [
            $dataProp => self::serializeItems($currentPageItems, $serializer),
            'pagination' => [
                'currentPage' => $paginator->getCurrentPage(),
                'pagesCount' => $paginator->getNbPages(),
                'itemsPerPage' => $paginator->getMaxPerPage(),
                'itemsInCurrentPage' => count($currentPageItems),
                'totalItems' => $paginator->getNbResults(),
            ],
        ];
    }

    /**
     * @param T[] $items
     * @param null|callable(T): array $serializer
     */
    private static function serializeItems(array $items, callable|null $serializer = null): array
    {
        return $serializer === null ? $items : array_map($serializer, $items);
    }

    /**
     * @param Pagerfanta<T> $paginator
     */
    public static function formatCurrentPageMessage(Pagerfanta $paginator, string $pattern): string
    {
        return sprintf($pattern, $paginator->getCurrentPage(), $paginator->getNbPages());
    }
}
