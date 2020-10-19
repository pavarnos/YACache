<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   5 11 2019
 */

declare(strict_types=1);

namespace LSS\YACache;

class RedisCache extends AbstractCache
{
    private string $prefix = '';

    private \Redis $redis;

    public function __construct(\Redis $redis, string $prefix = 'item')
    {
        $this->redis = $redis;
        $this->setPrefix($prefix);
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = rtrim($prefix, ':') . ':';
    }

    /** @inheritDoc */
    public function set(string $key, $value, int $ttl = self::NEVER_EXPIRES): void
    {
        if ($ttl != self::NEVER_EXPIRES) {
            $this->redis->set($this->makeKey($key), serialize($value), $ttl);
        } else {
            $this->redis->set($this->makeKey($key), serialize($value));
        }
    }

    /** @inheritDoc */
    public function has(string $key): bool
    {
        return $this->redis->exists($this->makeKey($key)) > 0;
    }

    /** @inheritDoc */
    public function get(string $key, $default = null)
    {
        $value = $this->redis->get($this->makeKey($key));
        if ($value === false) {
            return $default;
        }
        return is_numeric($value) ? $value : unserialize($value);
    }

    /** @inheritDoc */
    public function delete(string $key): void
    {
        $this->redis->del($this->makeKey($key));
    }

    /** @inheritDoc */
    public function clear(): bool
    {
        foreach ($this->getKeys() as $key) {
            $this->delete($key);
        }
        return true;
    }

    /** @inheritDoc */
    public function increment(string $key, int $ttl = self::NEVER_EXPIRES): int
    {
        $key   = $this->makeKey($key);
        $value = $this->redis->incr($key);
        if ($value === 1 && $ttl !== self::NEVER_EXPIRES) {
            $this->redis->expire($key, $ttl);
        }
        return $value;
    }

    private function getKeys(): array
    {
        $result = [];
        foreach ($this->redis->keys($this->prefix . '*') as $item) {
            $result[] = str_replace($this->prefix, '', $item);
        }
        return $result;
    }

    private function makeKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
