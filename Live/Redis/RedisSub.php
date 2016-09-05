<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午3:09
 */

namespace Live\Redis;

class RedisSub extends \Swoolet\Data\RedisAsync
{
    public $cfg_key = 'redis_async';

    public function __construct($cache_key = '')
    {
        $this->cache_key = $cache_key;
        parent::__construct();
    }
}