<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   12 Oct 2020
 */

declare(strict_types=1);

namespace LSS\YACache;

abstract class PrefixableCacheTestCase extends CacheTestCase
{
    public function testSetIsIndependent(): void
    {
        $key1 = 'one';
        $key2 = 'two';
        $subject1  = $this->getSubject('test1');
        $subject2 = $this->getSubject('test1a');
        self::assertFalse($subject1->has($key1));
        self::assertFalse($subject2->has($key1));

        // check caches do not set values in each other
        $subject1->set($key1, $value1 = 1);
        $subject2->set($key2, $value2 = 2);
        self::assertTrue($subject1->has($key1));
        self::assertFalse($subject2->has($key1));
        self::assertFalse($subject1->has($key2));
        self::assertFalse($subject2->has($key1));

        // check values are cached
        self::assertEquals($value1, $subject1->get($key1));
        self::assertEquals($value2, $subject2->get($key2));

        $subject1->clear();
        self::assertTrue($subject2->has($key2), 'clearing one should not clear the other');
    }

    abstract public function getSubject(string $prefix = ''): CacheInterface;
}
