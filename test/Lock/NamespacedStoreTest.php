<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Lock;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Lock\NamespacedStore;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\SharedLockStoreInterface;

class NamespacedStoreTest extends TestCase
{
    private MockObject & SharedLockStoreInterface $wrappedStore;

    public function setUp(): void
    {
        $this->wrappedStore = $this->createMock(SharedLockStoreInterface::class);
    }

    #[Test, DataProvider('provideKeysAndNamespaces')]
    public function keyIsReturnedVerbatimWhenNoNamespacesIsProvided(
        ?string $namespace,
        Key $key,
        string $expectedResource,
    ): void {
        $store = new NamespacedStore($this->wrappedStore, $namespace);
        $methods = [
            'save' => [$key],
            'delete' => [$key],
            'exists' => [$key],
            'putOffExpiration' => [$key, 123],
            'saveRead' => [$key],
        ];

        foreach ($methods as $method => $args) {
            $this->wrappedStore->expects($this->once())->method($method)->with(
                $this->callback(fn(Key $arg) => $arg->__toString() === $expectedResource),
            );

            $store->{$method}(...$args);
        }
    }

    public static function provideKeysAndNamespaces(): iterable
    {
        yield 'no namespace' => [null, new Key($expectedKey = 'base_resource'), $expectedKey];
        yield 'namespace already set' => ['shlink', new Key('shlink:base_resource'), 'shlink:base_resource'];
        yield 'namespace not set' => ['shlink', new Key('base_resource'), 'shlink:base_resource'];
    }
}
