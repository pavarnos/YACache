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

    protected function sleep(int $seconds): void
    {
        // apc relies on the system clock so we can't fake it with Carbon
        sleep($seconds);
        // sleep a little more to allow for rounding error
        sleep(1);
    }

    protected function tearDown(): void
    {
        (new APCCache())->clear();
        parent::tearDown();
    }
}
