<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   27 April 2015
 */

declare(strict_types=1);

namespace LSS\YACache;

/**
 * Usually an in-memory cache as primary and APC or Redis as secondary
 */
class TwoLevelCache extends AbstractCache
{
    private CacheInterface $primary;

    private CacheInterface $secondary;

    public function __construct(CacheInterface $primary, CacheInterface $secondary)
    {
        $this->primary   = $primary;
        $this->secondary = $secondary;
    }

    /** @inheritDoc */
    public function set(string $key, $value, int $ttl = self::NEVER_EXPIRES): void
    {
        $this->primary->set($key, $value, $ttl);
        $this->secondary->set($key, $value, $ttl);
    }

    /** @inheritDoc */
    public function has(string $key): bool
    {
        return $this->primary->has($key) || $this->secondary->has($key);
    }

    /** @inheritDoc */
    public function get(string $key, $default = null)
    {
        $result = $this->primary->get($key, $default);
        if ($result === $default) {
            $result = $this->secondary->get($key, $default);
        }
        return $result;
    }

    /** @inheritDoc */
    public function clear(): bool
    {
        $one = $this->primary->clear();
        $two = $this->secondary->clear();
        return $one && $two;
    }

    /** @inheritDoc */
    public function increment(string $key, int $ttl = self::NEVER_EXPIRES): int
    {
        $value = $this->primary->increment($key, $ttl);
        $this->secondary->increment($key, $ttl);
        return $value;
    }

    /** @inheritDoc */
    public function delete(string $key): void
    {
        $this->primary->delete($key);
        $this->secondary->delete($key);
    }
}
