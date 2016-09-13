<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午3:09
 */

namespace Live\Redis;


class RedisPub extends Common
{
    public $cfg_key = 'redis_async';

    public function publish($channel, $message)
    {
        return $this->link->publish($channel, $message);
    }
}