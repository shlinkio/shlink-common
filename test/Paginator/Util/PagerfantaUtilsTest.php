<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Paginator\Util;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Paginator\Util\PagerfantaUtils;

use function range;

/**
 * @template T
 */
class PagerfantaUtilsTest extends TestCase
{
    /**
     * @param Pagerfanta<T> $paginator
     */
    #[Test, DataProvider('providePaginatorAdapters')]
    public function paginatorIsSerializedAsExpected(array $expectedSerialization, Pagerfanta $paginator): void
    {
        $result = PagerfantaUtils::serializePaginator($paginator);
        self::assertEquals($expectedSerialization, $result);
    }

    public static function providePaginatorAdapters(): iterable
    {
        yield 'empty' => [
            [
                'data' => [],
                'pagination' => [
                    'currentPage' => 1,
                    'pagesCount' => 1,
                    'itemsPerPage' => 10,
                    'itemsInCurrentPage' => 0,
                    'totalItems' => 0,
                ],
            ],
            new Pagerfanta(new ArrayAdapter([])),
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
            (new Pagerfanta(new ArrayAdapter(range(1, 3))))->setMaxPerPage(2),
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
            (new Pagerfanta(new ArrayAdapter(range(1, 15))))->setMaxPerPage(3)->setCurrentPage(3),
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
            (new Pagerfanta(new ArrayAdapter(range(1, 13))))->setMaxPerPage(3)->setCurrentPage(5),
        ];
    }

    #[Test, DataProvider('provideDataProps')]
    public function paginatorIsSerializedWithExpectedDataProp(string $prop): void
    {
        $result = PagerfantaUtils::serializePaginator(new Pagerfanta(new ArrayAdapter([])), dataProp: $prop);

        self::assertArrayNotHasKey('data', $result);
        self::assertArrayHasKey($prop, $result);
    }

    public static function provideDataProps(): iterable
    {
        yield 'foo' => ['foo'];
        yield 'bar' => ['bar'];
        yield 'something' => ['something'];
    }

    #[Test]
    public function paginatorIsSerializedWithProvidedCallback(): void
    {
        ['data' => $data] = PagerfantaUtils::serializePaginator(
            new Pagerfanta(new ArrayAdapter(range(1, 10))),
            static fn (int $value) => $value * 2,
        );

        self::assertEquals([2, 4, 6, 8, 10, 12, 14, 16, 18, 20], $data);
    }

    /**
     * @param Pagerfanta<T> $paginator
     */
    #[Test, DataProvider('providePaginatorsToFormat')]
    public function pageMessageIsProperlyFormatted(
        string $expectedMessage,
        string $pattern,
        Pagerfanta $paginator,
    ): void {
        self::assertEquals($expectedMessage, PagerfantaUtils::formatCurrentPageMessage($paginator, $pattern));
    }

    public static function providePaginatorsToFormat(): iterable
    {
        yield [
            'Page "5" out of "10"',
            'Page "%s" out of "%s"',
            (new Pagerfanta(new ArrayAdapter(range(1, 20))))->setMaxPerPage(2)->setCurrentPage(5),
        ];
        yield [
            'Current: 1. Total: 1',
            'Current: %s. Total: %s',
            new Pagerfanta(new ArrayAdapter([1])),
        ];
    }
}
