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

    /**
     * @param $uid
     * @param $follow_uid 被关注人的uid
     * @return bool
     */
    public function follow($uid, $follow_uid)
    {
        if ($uid == $follow_uid || $this->isFollow($uid, $follow_uid) !== false)
            return false;

        $fan = $this->add($uid, $follow_uid);

        $follow = (new Follow())->add($follow_uid, $uid);

        return $fan;
    }

    public function unfollow($uid, $follow_uid)
    {
        if ($uid == $follow_uid)
            return false;

        $fan = $this->del($uid, $follow_uid);

        $follow = (new Follow())->del($follow_uid, $uid);

        return $fan;
    }

    public function isFollow($uid, $follow_uid)
    {
        $this->getList($uid, 0);
        return $this->cache->link->zScore($this->key . $uid, $follow_uid);
    }
}