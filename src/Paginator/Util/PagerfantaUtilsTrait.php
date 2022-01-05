<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Paginator\Util;

use Laminas\Stdlib\ArrayUtils;
use Pagerfanta\Pagerfanta;
use Shlinkio\Shlink\Common\Rest\DataTransformerInterface;

use function array_map;
use function count;
use function sprintf;

trait PagerfantaUtilsTrait
{
    private function serializePaginator(
        Pagerfanta $paginator,
        ?DataTransformerInterface $transformer = null,
        string $dataProp = 'data',
    ): array {
        $currentPageItems = ArrayUtils::iteratorToArray($paginator->getCurrentPageResults());

        return [
            $dataProp => $this->serializeItems($currentPageItems, $transformer),
            'pagination' => [
                'currentPage' => $paginator->getCurrentPage(),
                'pagesCount' => $paginator->getNbPages(),
                'itemsPerPage' => $paginator->getMaxPerPage(),
                'itemsInCurrentPage' => count($currentPageItems),
                'totalItems' => $paginator->getNbResults(),
            ],
        ];
    }

    private function serializeItems(array $items, ?DataTransformerInterface $transformer = null): array
    {
        return $transformer === null ? $items : array_map([$transformer, 'transform'], $items);
    }

    private function formatCurrentPageMessage(Pagerfanta $paginator, string $pattern): string
    {
        return sprintf($pattern, $paginator->getCurrentPage(), $paginator->getNbPages());
    }
}
