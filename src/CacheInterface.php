<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   12 Oct 2020
 */

declare(strict_types=1);

namespace LSS\YACache;

/**
 * Very simple cache
 * We don't implement PSR-6 or PSR-16 because we
 * - need an increment function,
 * - want strict types,
 * The interface otherwise follows PSR-16 as closely as possible (duck typing), pending a new stricter release by PHP-FIG
 */
interface CacheInterface
{
    public const NEVER_EXPIRES = 0;

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl
     */
    public function set(string $key, $value, int $ttl = self::NEVER_EXPIRES): void;

    /**
     * @param string     $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * @param string $key
     * @return bool true if $key exists in the cache
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @param int    $ttl
     * @return int
     */
    public function increment(string $key, int $ttl = self::NEVER_EXPIRES): int;

    /**
     * remove an item from the cache
     * @param string $key
     */
    public function delete(string $key): void;

    /**
     * delete all items in cache
     * @return bool true on success, false on failure
     */
    public function clear(): bool;

    /**
     * @param string   $key
     * @param int      $ttl
     * @param callable $create fn(): mixed returns the thing you want to cache if it is not in the cache
     * @return mixed
     */
    public function remember(string $key, int $ttl, callable $create);

    /**
     * @param string   $key
     * @param callable $create fn(): mixed returns the thing you want to cache if it is not in the cache
     * @return mixed
     */
    public function rememberForever(string $key, callable $create);

    /**
     * @param iterable<string> $keys
     * @param mixed|null       $default
     * @return array<string,mixed>
     */
    public function getMultiple(iterable $keys, $default = null): array;

    /**
     * @param iterable<string,mixed> $items keys => values of things to set in the cache
     * @param int                    $ttl
     */
    public function setMultiple(iterable $items, int $ttl = self::NEVER_EXPIRES): void;

    /**
     * @param iterable<string> $keys
     */
    public function deleteMultiple(iterable $keys): void;
}