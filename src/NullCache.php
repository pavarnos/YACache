<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   12 Oct 2020
 */

declare(strict_types=1);

namespace LSS\YACache;

/**
 * Never cache anything
 * Useful for testing
 */
class NullCache extends AbstractCache
{
    /** @inheritDoc */
    public function set(string $key, $value, int $ttl = 0): void
    {
    }

    /** @inheritDoc */
    public function get(string $key, $default = null)
    {
        return $default;
    }

    /** @inheritDoc */
    public function has(string $key): bool
    {
        return false;
    }

    /** @inheritDoc */
    public function increment(string $key, int $ttl = 0): int
    {
        return 1;
    }

    /** @inheritDoc */
    public function delete(string $key): void
    {
    }

    /** @inheritDoc */
    public function clear(): bool
    {
        return true;
    }
}