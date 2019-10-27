<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\Common\Doctrine\ReopeningEntityManager;
use stdClass;

class ReopeningEntityManagerTest extends TestCase
{
    /** @var ReopeningEntityManager */
    private $decoratorEm;
    /** @var ObjectProphecy */
    private $wrapped;
    /** @var bool  */
    private $factoryCallbackCalled = false;

    public function setUp(): void
    {
        $this->wrapped = $this->prophesize(EntityManagerInterface::class);
        $wrappedMock = $this->wrapped->reveal();

        $this->factoryCallbackCalled = false;
        $callCount = 0;

        $this->decoratorEm = new ReopeningEntityManager(function () use ($wrappedMock, &$callCount) {
            $callCount++;
            $this->factoryCallbackCalled = $callCount === 2;
            return $wrappedMock;
        });
    }

    /**
     * @test
     * @dataProvider provideMethodNames
     */
    public function wrappedInstanceIsTransparentlyCalledWhenItIsNotClosed(string $methodName): void
    {
        $method = $this->wrapped->__call($methodName, [Argument::cetera()])->willReturnArgument();
        $isOpen = $this->wrapped->isOpen()->willReturn(true);

        $this->decoratorEm->{$methodName}(new stdClass());

        $this->assertFalse($this->factoryCallbackCalled);
        $method->shouldHaveBeenCalledOnce();
        $isOpen->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     * @dataProvider provideMethodNames
     */
    public function wrappedInstanceIsRecreatedWhenItIsClosed(string $methodName): void
    {
        $method = $this->wrapped->__call($methodName, [Argument::cetera()])->willReturnArgument();
        $isOpen = $this->wrapped->isOpen()->willReturn(false);

        $this->decoratorEm->{$methodName}(new stdClass());

        $this->assertTrue($this->factoryCallbackCalled);
        $method->shouldHaveBeenCalledOnce();
        $isOpen->shouldHaveBeenCalledOnce();
    }

    public function provideMethodNames(): iterable
    {
        yield 'flush' => ['flush'];
        yield 'persist' => ['persist'];
        yield 'remove' => ['remove'];
        yield 'refresh' => ['refresh'];
        yield 'merge' => ['merge'];
    }
}
