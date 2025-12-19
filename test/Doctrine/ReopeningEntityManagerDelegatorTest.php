<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Shlinkio\Shlink\Common\Doctrine\ReopeningEntityManagerDelegator;

class ReopeningEntityManagerDelegatorTest extends TestCase
{
    #[Test]
    public function decoratesEntityManagerFromCallback(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $result = (new ReopeningEntityManagerDelegator())(new ServiceManager(), '', fn () => $em);

        $ref = new ReflectionObject($result);
        $prop = $ref->getProperty('wrapped');
        $prop->setAccessible(true);

        self::assertSame($em, $prop->getValue($result));
    }
}
