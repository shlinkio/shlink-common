<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Lock;

use Closure;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\SharedLockStoreInterface;

use function sprintf;
use function str_starts_with;

/**
 * Wraps a symfony/lock store and prefixes resources with a namespace.
 */
class NamespacedStore implements SharedLockStoreInterface
{
    public function __construct(
        private readonly SharedLockStoreInterface $wrappedStore,
        private readonly string|null $namespace = null,
        /** Some stores may not allow default separator value. Make sure you provide the appropriate one */
        private readonly string $namespaceSeparator = ':',
    ) {
    }

    public function save(Key $key): void
    {
        $this->wrappedStore->save($this->namespaceKey($key));
    }

    public function delete(Key $key): void
    {
        $this->wrappedStore->delete($this->namespaceKey($key));
    }

    public function exists(Key $key): bool
    {
        return $this->wrappedStore->exists($this->namespaceKey($key));
    }

    public function putOffExpiration(Key $key, float $ttl): void
    {
        $this->wrappedStore->putOffExpiration($this->namespaceKey($key), $ttl);
    }

    public function saveRead(Key $key): void
    {
        $this->wrappedStore->saveRead($this->namespaceKey($key));
    }

    private function namespaceKey(Key $key): Key
    {
        // If no namespace was provided, just use provided key verbatim
        if ($this->namespace === null) {
            return $key;
        }

        // If already prefixed, just use provided key verbatim
        $unprefixedResource = $key->__toString();
        $prefix = $this->namespace . $this->namespaceSeparator;
        if (str_starts_with($unprefixedResource, $prefix)) {
            return $key;
        }

        // Sadly, the key is mutated by wrapped store, and callers take this for granted. Creating a new instance would
        // make the reference get detached and things stop working.
        // Instead, we need to mutate provided key object to make sure things keep working.
        //
        // Using Closure::bind we can run a closure using provided key as $this context, and therefore, allowing private
        // props to be accessed
        return Closure::bind(function (Key $mutableKey) use ($prefix, $unprefixedResource) {
            $mutableKey->resource = sprintf('%s%s', $prefix, $unprefixedResource);
            return $mutableKey;
        }, null, $key)($key);
    }
}
