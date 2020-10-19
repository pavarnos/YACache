<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   12 Oct 2020
 */

declare(strict_types=1);

namespace LSS\YACache;

class MemoryCacheTest extends CacheTestCase
{
    public function getSubject(): CacheInterface
    {
        return new MemoryCache();
    }
}
