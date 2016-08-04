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

    public $limit = 5000;

    public function beFan($uid, $follow_uid)
    {
        if ($this->isFollow($uid, $follow_uid) === false) {
            $fan = $this->add($uid, $follow_uid);

            $db_follow = new Follow();
            $follow = $db_follow->add($follow_uid, $uid);

            return $fan;
        }

        return false;
    }

    public function isFollow($uid, $follow_uid)
    {
        $this->getList($uid, 0);
        return $this->cache->link->zScore($this->key . $uid, $follow_uid);
    }
}