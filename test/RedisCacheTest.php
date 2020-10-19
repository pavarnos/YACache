<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   31 7 2018
 */

declare(strict_types=1);

namespace LSS\YACache;

class RedisCacheTest extends PrefixableCacheTestCase
{
    public function getSubject(string $prefix = ''): CacheInterface
    {
        $redis = new FakeRedis();
        return new RedisCache($redis, $prefix);
    }
}
