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

    const DEFAULT_TIMEOUT = 2592000;//30天

    public function set($key, $val, $ttl = 0)
    {
        $ttl or $ttl = self::DEFAULT_TIMEOUT;

//        if (is_array($val))
//            $val = json_encode($val, \JSON_UNESCAPED_UNICODE);

        $val = \msgpack_pack($val);

        if (!$ret = $this->link->set($key, $val, $ttl))
            Response::msg('R错误', 100);

        return $ret;
    }

    public function get($key)
    {
        if ($ret = $this->link->get($key))
            $ret = \msgpack_unpack($ret);//return json_decode($ret, true);

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

    public function revRange($key, $score, $limit, $with_score)
    {
        return $this->link->zRevRange($key, $score, $limit - 1, $with_score);
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