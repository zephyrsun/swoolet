<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午3:09
 */

namespace Live\Redis;


class Share extends Common
{
    public $cfg_key = 'redis_1';

    public $key_shared = 'shared:';

    public function setShared($uid)
    {
        $n = 1;
        return $n == $this->incrCount($this->key_shared . $uid, $n, 86400);
    }
}