<?php
/**
 * @file
 * @author  Lightly Salted Software
 * @date    27 April 2015
 */

declare(strict_types=1);

namespace LSS\YACache;

class TwoLevelCacheTest extends CacheTestCase
{
    private CacheInterface $primary;

    private CacheInterface $secondary;

    public function getSubject(): CacheInterface
    {
        $this->primary   = new MemoryCache();
        $this->secondary = new MemoryCache();
        return new TwoLevelCache($this->primary, $this->secondary);
    }

    public function testTwoLevelsSet(): void
    {
        $subject = $this->getSubject();
        $key     = 'the key';
        $value   = [123 => 'abcdef'];
        self::assertFalse($subject->has($key));
        $subject->set($key, $value);
        self::assertTrue($subject->has($key));
        self::assertTrue($this->primary->has($key));
        self::assertTrue($this->secondary->has($key));
    }

    public function testTwoLevelsHas(): void
    {
        $subject = $this->getSubject();
        $key     = 'the key';
        $value   = [123 => 'abcdef'];
        $this->secondary->set($key, $value);
        self::assertFalse($this->primary->has($key));
        self::assertTrue($this->secondary->has($key));
        self::assertTrue($subject->has($key)); // should reach through to the secondary if not in primary
    }

    public function testTwoLevelsGet(): void
    {
        $subject = $this->getSubject();
        $key     = 'the key';
        $value   = [123 => 'abcdef'];
        $this->secondary->set($key, $value);
        self::assertEquals($value, $subject->get($key)); // should reach through to the secondary if not in primary
    }
}
