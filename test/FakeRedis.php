<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * @date   6 7 2018
 */

declare(strict_types=1);

namespace LSS\YACache;

/**
 * Thanks to https://github.com/M6Web/RedisMock/blob/master/src/M6Web/Component/RedisMock/RedisMock.php
 * For the ideas
 */
class FakeRedis extends \Redis
{
    /** @var array */
    public array $hash = [];

    /** @var array */
    public array $list = [];

    /** @var array */
    public array $value = [];

    /** @var array */
    public array $expires = [];

    /** @var bool */
    private bool $pipeline = false;

    /** @var array */
    private array $pipedInfo = [];

    /**
     * @inheritDoc
     */
    public function info($mode = null): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function keys($pattern)
    {
        $pattern = preg_replace('#\*#', '.*', $pattern);
        $pattern = preg_replace('#\?#', '.', $pattern);
        $pattern = preg_replace('#(\[[^\]]+\])#', '$1+', $pattern);
        $result  = [];
        foreach (array_merge(array_keys($this->hash), array_keys($this->list), array_keys($this->value)) as $key) {
            if (preg_match('#^' . $pattern . '$#', (string)$key) > 0 && !$this->deleteExpired((string)$key)) {
                $result[] = $key;
            }
        }
        return $this->returnPipedInfo($result);
    }

    /**
     * @inheritDoc
     */
    public function del($key1, ...$otherKeys)
    {
        foreach (array_filter(array_merge((array)$key1, $otherKeys)) as $key) {
            unset($this->hash[$key], $this->expires[$key], $this->list[$key], $this->value[$key]);
        }
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $timeout = null, $opt = null)
    {
        $this->value[$key] = $value;
        if (!is_null($timeout)) {
            $this->expires[$key] = time() + $timeout;
        }
        $this->returnPipedInfo('OK');
    }

    /**
     * @inheritDoc
     */
    public function get($key)
    {
        $this->deleteExpired($key);
        return $this->returnPipedInfo($this->value[$key] ?? false);
    }

    /**
     * @inheritDoc
     */
    public function exists($key, ...$otherKeys)
    {
        $this->deleteExpired($key);
        return $this->returnPipedInfo(
            (isset($this->value[$key]) || isset($this->hash[$key]) || isset($this->list[$key])) ? 1 : 0
        );
    }

    /**
     * @inheritDoc
     */
    public function incr($key)
    {
        $value = intval($this->get($key) ?: 0) + 1;
        $this->set($key, $value);
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function lLen($key)
    {
        return $this->returnPipedInfo(count($this->list[$key] ?? []));
    }

    /**
     * @inheritDoc
     */
    public function lRange($key, $start, $stop)
    {
        $list = $this->list[$key] ?? [];
        if ($start < 0) {
            if (intval(abs($start)) > count($list)) {
                $start = 0;
            } else {
                $start = count($list) + $start;
            }
        }
        // intvals are for phpstan because it thinks $stop and $start are float|int for some weird reason
        $start = intval($start);
        $stop  = intval($stop);
        if ($stop >= 0) {
            $length = $stop - $start + 1;
        } else {
            if ($stop == -1) {
                $length = null;
            } else {
                $length = $stop + 1;
            }
        }
        return $this->returnPipedInfo(array_slice($list, $start, $length));
    }

    /**
     * @inheritDoc
     */
    public function lRem($key, $value, $count)
    {
        assert($count == 0, 'cannot currently handle other cases');
        $list             = $this->list[$key] ?? [];
        $before           = count($list);
        $this->list[$key] = array_filter(
            $list,
            function ($current) use ($value) {
                return $current != $value;
            }
        );
        return $this->returnPipedInfo($before - count($this->list[$key]));
    }

    /**
     * @inheritDoc
     */
    public function rPush($key, $value1)
    {
        $this->list[$key][] = $value1;
        return $this->returnPipedInfo(count($this->list[$key]));
    }

    /**
     * @inheritDoc
     */
    public function lPush($key, $value1)
    {
        if (empty($this->list[$key])) {
            $this->list[$key] = [$value1];
        } else {
            array_unshift($this->list[$key], $value1);
        }
        return $this->returnPipedInfo(count($this->list[$key]));
    }

    /**
     * @inheritDoc
     */
    public function rpoplpush($srcKey, $dstKey)
    {
        if (empty($this->list[$srcKey])) {
            return $this->returnPipedInfo(null);
        }
        $value = array_pop($this->list[$srcKey]);
        if (empty($this->list[$dstKey])) {
            $this->list[$dstKey] = [$value];
        } else {
            array_unshift($this->list[$dstKey], $value);
        }
        return $this->returnPipedInfo($value);
    }

    /**
     * @inheritDoc
     */
    public function brpoplpush($srcKey, $dstKey, $timeout)
    {
        // ignore the timeout for testing
        return $this->rpoplpush($srcKey, $dstKey);
    }

    /**
     * @inheritDoc
     */
    public function hIncrBy($key, $value, $amount)
    {
        $this->deleteExpired($key);
        return $this->returnPipedInfo($this->hash[$key][$value] = ($this->hash[$key][$value] ?? 0) + intval($amount));
    }

    /**
     * @inheritDoc
     */
    public function hSet($key, $hashKey, $value)
    {
        $this->hash[$key][$hashKey] = $value;
        return $this->returnPipedInfo(1); // not strictly correct, but we don;t use the return value so who cares
    }

    /**
     * @inheritDoc
     */
    public function hGet($key, $value)
    {
        $this->deleteExpired($key);
        return $this->returnPipedInfo($this->hash[$key][$value] ?? false);
    }

    /**
     * @inheritDoc
     */
    public function hDel($key, $value, ...$v2)
    {
        $this->deleteExpired($key);
        unset($this->hash[$key][$value]);
    }

    /**
     * @inheritDoc
     */
    public function hGetAll($key)
    {
        $this->deleteExpired($key);
        return $this->returnPipedInfo($this->hash[$key] ?? []);
    }

    /**
     * @inheritDoc
     */
    public function hMSet($key, $hashKeys)
    {
        $this->hash[$key] = array_merge($this->hash[$key] ?? [], $hashKeys);
        return $this->returnPipedInfo('OK');
    }

    /**
     * @inheritDoc
     */
    public function expireAt($key, $time)
    {
        $this->expires[$key] = $time;
        return $this->returnPipedInfo(1);
    }

    /**
     * @inheritDoc
     */
    public function expire($key, $ttl)
    {
        $this->expires[$key] = time() + $ttl;
        return $this->returnPipedInfo(1);
    }

    /**
     * @inheritDoc
     */
    public function ttl($key)
    {
        return $this->returnPipedInfo($this->expires[$key] ?? -2);
    }

    // Transactions

    /**
     * @inheritDoc
     */
    public function multi($mode = self::MULTI)
    {
        $this->pipeline  = true;
        $this->pipedInfo = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function discard()
    {
        $this->pipeline  = false;
        $this->pipedInfo = [];
        return 'OK';
    }

    /**
     * @inheritDoc
     */
    public function exec()
    {
        $pipedInfo = $this->pipedInfo;
        $this->discard();
        return $pipedInfo;
    }

    // Client pipeline
    public function pipeline()
    {
        $this->pipeline = true;
        return $this;
    }

    public function execute()
    {
        $this->pipeline = false;
        return $this;
    }

    // Protected methods
    protected function stopPipeline()
    {
        $this->pipeline = false;
    }

    /**
     * @param int|string|array|null $info
     * @return $this|string|array
     */
    protected function returnPipedInfo($info)
    {
        if (!$this->pipeline) {
            return $info;
        }
        $this->pipedInfo[] = $info;
        return $this;
    }

    protected function deleteExpired(string $key)
    {
        $time = time();
        if (isset($this->expires[$key]) && $this->expires[$key] < $time) {
            $this->del($key);
            return true;
        }
        return false;
    }
}