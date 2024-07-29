<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Paginator;

use Pagerfanta\Adapter\AdapterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Paginator\Paginator;

/**
 * @template T
 */
class PaginatorTest extends TestCase
{
    /** @var Paginator<T> */
    private Paginator $paginator;
    /** @var MockObject & AdapterInterface<T> */
    private MockObject & AdapterInterface $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->paginator = new Paginator($this->adapter);
    }

    /**
     * @param -1|int<1, max> $maxPage
     */
    #[Test, DataProvider('providePositiveNumbers')]
    public function setMaxBehavesAsUsualWhenPositiveNumberIsProvided(int $maxPage): void
    {
        $this->adapter->expects($this->never())->method('getNbResults');

        $this->paginator->setMaxPerPage($maxPage);

        self::assertEquals($maxPage, $this->paginator->getMaxPerPage());
    }

    public static function providePositiveNumbers(): iterable
    {
        return [[5], [15], [32], [1], [8]];
    }

    /**
     * @param -1|int<1, max> $maxPage
     */
    #[Test, DataProvider('provideNonPositiveNumbers')]
    public function setMaxFallsBackToAdapterWhenNonPositiveNumberIsProvided(int $maxPage): void
    {
        $expected = 35;
        $this->adapter->expects($this->once())->method('getNbResults')->willReturn($expected);

        $this->paginator->setMaxPerPage($maxPage);

        self::assertEquals($expected, $this->paginator->getMaxPerPage());
    }

    public static function provideNonPositiveNumbers(): iterable
    {
        return [[-3], [-25], [0], [-1]];
    }

    #[Test, DataProvider('provideEmptyAdapterResults')]
    public function getMaxReturnsOneWhenAdapterReturnsEmpty(int $adapterNbResults): void
    {
        $this->adapter->expects($this->once())->method('getNbResults')->willReturn($adapterNbResults);

        $this->paginator->setMaxPerPage(-1);

        self::assertEquals(1, $this->paginator->getMaxPerPage());
    }

    public static function provideEmptyAdapterResults(): iterable
    {
        return [[-3], [-25], [0], [-1]];
    }
}
