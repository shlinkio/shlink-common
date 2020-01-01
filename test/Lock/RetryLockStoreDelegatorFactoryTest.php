<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Lock;

use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Shlinkio\Shlink\Common\Lock\RetryLockStoreDelegatorFactory;
use Symfony\Component\Lock\PersistingStoreInterface;
use Zend\ServiceManager\ServiceManager;

class RetryLockStoreDelegatorFactoryTest extends TestCase
{
    private RetryLockStoreDelegatorFactory $delegator;
    private PersistingStoreInterface $originalStore;

    public function setUp(): void
    {
        $this->originalStore = $this->prophesize(PersistingStoreInterface::class)->reveal();
        $this->delegator = new RetryLockStoreDelegatorFactory();
    }

    /** @test */
    public function originalStoreIsWrappedInRetryStore(): void
    {
        $callback = fn () => $this->originalStore;

        $result = ($this->delegator)(new ServiceManager(), '', $callback);

        $ref = new ReflectionObject($result);
        $prop = $ref->getProperty('decorated');
        $prop->setAccessible(true);

        $this->assertSame($this->originalStore, $prop->getValue($result));
    }
}
