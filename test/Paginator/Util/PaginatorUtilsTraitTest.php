<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Paginator\Util;

use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Paginator\Util\PaginatorUtilsTrait;

use function range;

class PaginatorUtilsTraitTest extends TestCase
{
    use PaginatorUtilsTrait;

    /**
     * @test
     * @dataProvider providePaginatorAdapters
     */
    public function paginatorIsSerializedAsExpected(array $expectedSerialization, Paginator $paginator): void
    {
        $result = $this->serializePaginator($paginator);
        $this->assertEquals($expectedSerialization, $result);
    }

    public function providePaginatorAdapters(): iterable
    {
        yield 'empty' => [
            [
                'data' => [],
                'pagination' => [
                    'currentPage' => 1,
                    'pagesCount' => 0,
                    'itemsPerPage' => 10,
                    'itemsInCurrentPage' => 0,
                    'totalItems' => 0,
                ],
            ],
            new Paginator(new ArrayAdapter([])),
        ];

        yield 'with two pages' => [
            [
                'data' => [1, 2],
                'pagination' => [
                    'currentPage' => 1,
                    'pagesCount' => 2,
                    'itemsPerPage' => 2,
                    'itemsInCurrentPage' => 2,
                    'totalItems' => 3,
                ],
            ],
            (new Paginator(new ArrayAdapter(range(1, 3))))->setItemCountPerPage(2),
        ];

        yield 'not in first page' => [
            [
                'data' => [7, 8, 9],
                'pagination' => [
                    'currentPage' => 3,
                    'pagesCount' => 5,
                    'itemsPerPage' => 3,
                    'itemsInCurrentPage' => 3,
                    'totalItems' => 15,
                ],
            ],
            (new Paginator(new ArrayAdapter(range(1, 15))))->setItemCountPerPage(3)->setCurrentPageNumber(3),
        ];

        yield 'last incomplete page' => [
            [
                'data' => [13],
                'pagination' => [
                    'currentPage' => 5,
                    'pagesCount' => 5,
                    'itemsPerPage' => 3,
                    'itemsInCurrentPage' => 1,
                    'totalItems' => 13,
                ],
            ],
            (new Paginator(new ArrayAdapter(range(1, 13))))->setItemCountPerPage(3)->setCurrentPageNumber(5),
        ];
    }

    /**
     * @test
     * @dataProvider providePaginatorsInPage
     */
    public function properlyTellsIfInLastPage(bool $expectedInLastPage, Paginator $paginator): void
    {
        $this->assertEquals($expectedInLastPage, $this->isLastPage($paginator));
    }

    public function providePaginatorsInPage(): iterable
    {
        yield 'empty in last page' => [true, new Paginator(new ArrayAdapter([]))];
        yield 'not empty in last page' => [
            true,
            (new Paginator(new ArrayAdapter(range(1, 15))))->setCurrentPageNumber(5),
        ];
        yield 'not in last page' => [false, new Paginator(new ArrayAdapter(range(1, 15)))];
    }

    /**
     * @test
     * @dataProvider providePaginatorsToFormat
     */
    public function pageMessageIsProperlyFormatted(string $expectedMessage, string $pattern, Paginator $paginator): void
    {
        $this->assertEquals($expectedMessage, $this->formatCurrentPageMessage($paginator, $pattern));
    }

    public function providePaginatorsToFormat(): iterable
    {
        yield [
            'Page "5" out of "10"',
            'Page "%s" out of "%s"',
            (new Paginator(new ArrayAdapter(range(1, 20))))->setCurrentPageNumber(5)->setItemCountPerPage(2),
        ];
        yield [
            'Current: 1. Total: 1',
            'Current: %s. Total: %s',
            new Paginator(new ArrayAdapter([1])),
        ];
    }
}
