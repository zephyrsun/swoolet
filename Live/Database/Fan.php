<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午2:59
 */

namespace Live\Database;

class Fan extends Follow
{
    public $table_prefix = 'fan_';
    public $key = 'fan:';
    public $key_count = 'fan_n:';

    public function beFan($uid, $follow_uid)
    {
        $fan = $this->add($uid, $follow_uid);

        $db_follow = new Follow();
        $follow = $db_follow->add($follow_uid, $uid);

        return $fan;
    }
}