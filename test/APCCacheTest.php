<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   12 Oct 2020
 */

declare(strict_types=1);

namespace LSS\YACache;

class APCCacheTest extends PrefixableCacheTestCase
{
    public function getSubject(string $prefix = ''): CacheInterface
    {
        return new APCCache($prefix);
    }

    protected function tearDown(): void
    {
        (new APCCache())->clear();
        parent::tearDown();
    }
}
