<?php
/**
 * @file
 * @author  Lightly Salted Software
 * @date    27 April 2015
 */

declare(strict_types=1);

namespace LSS\YACache;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

abstract class CacheTestCase extends TestCase
{
    abstract public function getSubject(): CacheInterface;

    public function testSet(): void
    {
        $key     = 'the key';
        $subject = $this->getSubject();
        $subject->set($key, $value = ['abc' => 123]);
        self::assertTrue($subject->has($key));
        self::assertEquals($value, $subject->get($key));
    }

    public function testDelete(): void
    {
        $key     = 'the key';
        $subject = $this->getSubject();
        $subject->set($key, $value = ['abc' => 123]);
        self::assertTrue($subject->has($key));
        $subject->delete($key);
        self::assertFalse($subject->has($key));
        self::assertEquals(null, $subject->get($key));
    }

    public function testHas(): void
    {
        $key     = 'the key';
        $subject = $this->getSubject();
        self::assertFalse($subject->has($key));
//        self::assertFalse($subject->has(null));
//        self::assertFalse($subject->has(''));

        $subject->set($key, $value = ['abc' => 123]);
        self::assertTrue($subject->has($key));
    }

    public function testClear(): void
    {
        $key     = 'the key';
        $subject = $this->getSubject();
        $subject->set($key, $value = ['abc' => 123]);
        $subject->clear();
        self::assertFalse($subject->has($key));
        self::assertEquals(null, $subject->get($key));
    }

    public function testIncrement(): void
    {
        $key     = 'the key';
        $subject = $this->getSubject();
        self::assertEquals(0, $subject->get($key, 0));

        self::assertEquals(1, $subject->increment($key));
        self::assertEquals(1, $subject->get($key, 0));

        self::assertEquals(2, $subject->increment($key));
        self::assertEquals(2, $subject->get($key, 0));
    }

    public function testTTL(): void
    {
        $value3  = 'three';
        $subject = $this->getSubject();
        $subject->set($key1 = 'one', $value = '111', $ttl = 1);
        $subject->increment($key2 = 'two', $ttl);
        $subject->increment($key2);
        $subject->remember($key3 = 'three', $ttl, fn() => $value3);
        self::assertTrue($subject->has($key1));
        self::assertTrue($subject->has($key2));
        self::assertTrue($subject->has($key3));
        self::assertEquals($value3, $subject->get($key3));
        // items should all expire from cache in $ttl seconds
        $this->sleep($ttl);
        self::assertFalse($subject->has($key1));
        self::assertFalse($subject->has($key2));
        self::assertFalse($subject->has($key3));
    }

    public function testRememberForever(): void
    {
        $callCount = 0;
        $key       = 'the key';
        $value     = 'some value';
        $subject   = $this->getSubject();
        $generator = function () use (&$callCount, $value): string {
            $callCount++;
            return $value;
        };
        self::assertEquals($value, $subject->rememberForever($key, $generator));
        self::assertEquals($value, $subject->rememberForever($key, $generator));
        self::assertEquals($value, $subject->rememberForever($key, $generator));
        self::assertEquals(1, $callCount, 'generator should only be called once');
    }

    public function testMultiple(): void
    {
        $key1    = 'one';
        $key2    = 'two';
        $value1  = 1111;
        $value2  = 222;
        $default = 'default value';
        $subject = $this->getSubject();
        // not set yet
        self::assertEquals(
            [$key1 => $default, $key2 => $default, 'no-such' => $default],
            $subject->getMultiple([$key1, $key2, 'no-such'], $default)
        );

        // set order should not matter
        $subject->setMultiple([$key2 => $value2, $key1 => $value1]);

        // items should come out in the order you ask them
        self::assertEquals(
            [$key1 => $value1, 'no-such' => $default, $key2 => $value2],
            $subject->getMultiple([$key1, 'no-such', $key2], $default)
        );

        $subject->deleteMultiple([$key2, 'no-such']);
        self::assertTrue($subject->has($key1));
        self::assertFalse($subject->has($key2));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function sleep(int $seconds): void
    {
        Carbon::setTestNow(Carbon::now()->addSeconds($seconds));
    }
}
