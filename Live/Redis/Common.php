<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午3:09
 */

namespace Live\Redis;

use Live\Response;
use Swoolet\Data\Redis;

class Common extends Redis
{
    public $cfg_key = 'redis_1';

    public function set($key, $val, $ttl = 0)
    {
        $ttl or $ttl = 86400 * 3;

        return $this->link->set($key, \msgpack_pack($val), $ttl);
    }

    public function add($key, $val, $ttl = 0)
    {
        $ttl or $ttl = 86400 * 3;

        if ($ret = $this->link->setnx($key, \msgpack_pack($val)))
            $this->expire($key, $ttl);

        return $ret;
    }

    public function get($key)
    {
        if ($ret = $this->link->get($key))
            $ret = \msgpack_unpack($ret);

        return $ret;
    }

    public function getCount($key)
    {
        return (int)$this->link->get($key);
    }

    public function incrCount($key, $n, $ttl)
    {
        $ret = $this->link->incrBy($key, $n);
        if ($ret == $n)
            $this->expire($key, $ttl);

        return $ret;
    }

    public function revRange($key, $start, $limit, $with_score)
    {
        if ($start > 0)
            return $this->link->zRevRangeByScore($key, $start, '-inf', ['limit' => [1, $limit], 'withscores' => $with_score]);

        return $this->link->zRevRange($key, $start, $limit - 1, $with_score);
    }

    public function expire($key, $ttl)
    {
        return $this->link->expire($key, $ttl);
    }

    public function del($key)
    {
        return $this->link->del($key);
    }
}