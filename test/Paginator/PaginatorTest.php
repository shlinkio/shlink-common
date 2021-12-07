<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Paginator;

use Pagerfanta\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\Common\Paginator\Paginator;

class PaginatorTest extends TestCase
{
    use ProphecyTrait;

    private Paginator $paginator;
    private ObjectProphecy $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->prophesize(AdapterInterface::class);
        $this->paginator = new Paginator($this->adapter->reveal());
    }

    /**
     * @test
     * @dataProvider providePositiveNumbers
     */
    public function setMaxBehavesAsUsualWhenPositiveNumberIsProvided(int $maxPage): void
    {
        $this->paginator->setMaxPerPage($maxPage);

        self::assertEquals($maxPage, $this->paginator->getMaxPerPage());
        $this->adapter->getNbResults()->shouldNotHaveBeenCalled();
    }

    public function providePositiveNumbers(): iterable
    {
        return [[5], [15], [32], [1], [8]];
    }

    /**
     * @test
     * @dataProvider provideNonPositiveNumbers
     */
    public function setMaxFallsBackToAdapterWhenNonPositiveNumberIsProvided(int $maxPage): void
    {
        $expected = 35;
        $getFromAdapter = $this->adapter->getNbResults()->willReturn($expected);

        $this->paginator->setMaxPerPage($maxPage);

        self::assertEquals($expected, $this->paginator->getMaxPerPage());
        $getFromAdapter->shouldHaveBeenCalledOnce();
    }

    public function provideNonPositiveNumbers(): iterable
    {
        return [[-3], [-25], [0], [-1]];
    }

    /**
     * @test
     * @dataProvider provideEmptyAdapterResults
     */
    public function getMaxReturnsOneWhenAdapterReturnsEmpty(int $adapterNbResults): void
    {
        $getFromAdapter = $this->adapter->getNbResults()->willReturn($adapterNbResults);

        $this->paginator->setMaxPerPage(-1);

        self::assertEquals(1, $this->paginator->getMaxPerPage());
        $getFromAdapter->shouldHaveBeenCalledOnce();
    }

    public function provideEmptyAdapterResults(): iterable
    {
        return [[-3], [-25], [0], [-1]];
    }
}
