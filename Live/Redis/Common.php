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

    public function set($key, $val, $timeout = 0)
    {
        $timeout or $timeout = self::DEFAULT_TIMEOUT;

        if (is_array($val))
            $val = json_encode($val, \JSON_UNESCAPED_UNICODE);

        if (!$ret = $this->link->set($key, $val, $timeout))
            Response::msg('R错误', 100);

        return $ret;
    }

    public function get($key)
    {
        if ($ret = $this->link->get($key))
            return json_decode($ret, true);

        return $ret;
    }
}