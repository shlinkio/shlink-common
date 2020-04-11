<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Shlinkio\Shlink\Common\Doctrine\ReopeningEntityManager;

class ReopeningEntityManagerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     * @dataProvider provideWrapped
     */
    public function wrappedEntityManagerIsOnlyRecreatedWhenCurrentOneIsClosed(
        EntityManagerInterface $wrapped,
        bool $shouldRecreate
    ): void {
        $factoryCalls = 0;
        $reopeningEm = new ReopeningEntityManager(static function () use ($wrapped, &$factoryCalls) {
            $factoryCalls++;
            return $wrapped;
        });

        $reopeningEm->open();

        $this->assertEquals($shouldRecreate, $factoryCalls === 2);
    }

    public function provideWrapped(): iterable
    {
        $createEmMock = function (bool $isOpen): EntityManagerInterface {
            $em = $this->prophesize(EntityManagerInterface::class);
            $em->isOpen()->willReturn($isOpen);

            return $em->reveal();
        };

        yield [$createEmMock(true), false];
        yield [$createEmMock(false), true];
    }
}
