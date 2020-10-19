<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   19 Oct 2020
 */

declare(strict_types=1);

namespace LSS\YACache;

abstract class AbstractCache implements CacheInterface
{
    public function remember(string $key, int $ttl, callable $create)
    {
        $result = $this->get($key);
        if ($result === null) {
            $result = $create();
            $this->set($key, $result, $ttl);
        }
        return $result;
    }

    public function rememberForever(string $key, callable $create)
    {
        return $this->remember($key, CacheInterface::NEVER_EXPIRES, $create);
    }

    public function getMultiple(iterable $keys, $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple(iterable $items, int $ttl = self::NEVER_EXPIRES): void
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value, $ttl);
        }
    }

    public function deleteMultiple(iterable $keys): void
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }
}