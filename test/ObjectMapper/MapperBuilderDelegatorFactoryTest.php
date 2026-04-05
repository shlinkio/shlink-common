<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\ObjectMapper;

use Cake\Chronos\Chronos;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\ObjectMapper\MapperBuilderDelegatorFactory;

class MapperBuilderDelegatorFactoryTest extends TestCase
{
    private TreeMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new MapperBuilderDelegatorFactory()(
            container: $this->createStub(ContainerInterface::class),
            name: '',
            callback: fn () => new MapperBuilder(),
        )->mapper();
    }

    #[Test]
    public function normalizesStrings(): void
    {
        self::assertEquals('the string', $this->mapper->map('string', '  the <span>string</span>  '));
        self::assertEquals(
            'the string in obj',
            $this->mapper->map(MockModel::class, ['foo' => ' the <span>string</span> in obj  '])->foo,
        );
    }

    #[Test]
    public function normalizesDates(): void
    {
        $date = Chronos::now()->setMicrosecond(0);

        self::assertEquals($date, $this->mapper->map(Chronos::class, $date->toAtomString()));
        self::assertEquals($date, $this->mapper->map(DateTimeInterface::class, $date->toAtomString()));
        self::assertEquals(
            $date,
            $this->mapper->map(MockModel::class, ['bar' => $date->toAtomString()])->bar,
        );

        self::assertEquals($date->startOfDay(), $this->mapper->map(Chronos::class, $date->format('Y-m-d')));
        self::assertEquals($date->startOfDay(), $this->mapper->map(DateTimeInterface::class, $date->format('Y-m-d')));
        self::assertEquals(
            $date->startOfDay(),
            $this->mapper->map(MockModel::class, ['bar' => $date->format('Y-m-d')])->bar,
        );
    }
}
