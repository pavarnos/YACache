<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   12 Oct 2020
 */

declare(strict_types=1);

namespace LSS\YACache;

class APCCache extends AbstractCache
{
    private string $prefix;

    public function __construct(string $prefix = '')
    {
        assert(strpos($prefix, ':') === false);
        if (!empty($prefix)) {
            $prefix .= ':';
        }
        $this->prefix = $prefix;
    }

    /** @inheritDoc */
    public function set(string $key, $value, int $ttl = 0): void
    {
        apcu_store($this->prefix . $key, $value, $ttl);
    }

    /** @inheritDoc */
    public function get(string $key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }
        return apcu_fetch($this->prefix . $key) ?: $default;
    }

    /** @inheritDoc */
    public function has(string $key): bool
    {
        return !empty(apcu_exists($this->prefix . $key));
    }

    /** @inheritDoc */
    public function increment(string $key, int $ttl = 0): int
    {
        return apcu_inc($this->prefix . $key, 1, $ignored, $ttl);
    }

    /** @inheritDoc */
    public function delete(string $key): void
    {
        apcu_delete($this->prefix . $key);
    }

    /** @inheritDoc */
    public function clear(): bool
    {
        if (empty($this->prefix)) {
            return apcu_clear_cache();
        } else {
            return apcu_delete(new \APCUIterator('|^' . $this->prefix . '.*|')) === true;
        }
    }
}