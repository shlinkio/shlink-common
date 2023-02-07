<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Doctrine\ReopeningEntityManager;

class ReopeningEntityManagerTest extends TestCase
{
    #[Test, DataProvider('provideWrapped')]
    public function wrappedEntityManagerIsOnlyRecreatedWhenCurrentOneIsClosed(
        callable $wrappedEntityManagerCreator,
        bool $shouldRecreate,
    ): void {
        $wrapped = $wrappedEntityManagerCreator($this);
        $factoryCalls = 0;
        $reopeningEm = new ReopeningEntityManager(static function () use ($wrapped, &$factoryCalls) {
            $factoryCalls++;
            return $wrapped;
        });

        $reopeningEm->open();

        self::assertEquals($shouldRecreate, $factoryCalls === 2);
    }

    public static function provideWrapped(): iterable
    {
        $createEmMock = static fn (bool $isOpen) => function (TestCase $test) use ($isOpen): EntityManagerInterface {
            $em = $test->createMock(EntityManagerInterface::class);
            $em->method('isOpen')->willReturn($isOpen);

            return $em;
        };

        yield [$createEmMock(true), false];
        yield [$createEmMock(false), true];
    }
}
