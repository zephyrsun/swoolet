<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午3:09
 */

namespace Live\Redis;


use Swoolet\Data\Redis;

class UserExt extends Redis
{
    public $cfg_key = 'redis_1';

    public $key_ext = 'user_ext:';

    public function incr($uid, $k, $v)
    {
        return $this->link->hIncrBy($this->key_ext . $uid, $k, $v);
    }

    public function set($uid, $k, $v)
    {
        return $this->link->hSet($this->key_ext . $uid, $k, $v);
    }

    public function get($uid, $k)
    {
        return $this->link->hGet($this->key_ext . $uid, $k);
    }

    public function del($uid, $k)
    {
        return $this->link->hDel($this->key_ext . $uid, $k);
    }

    public function mSet($uid, $v)
    {
        return $this->link->hMset($this->key_ext . $uid, $v);
    }

    public function getWithCallback($uid, $k, $callback)
    {
        if (!$ret = $this->get($uid, $k)) {
            if ($ret = (string)$callback())
                $this->set($uid, 'sent', $ret);
        }

        return $ret;

    }

}