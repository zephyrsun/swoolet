<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午2:59
 */

namespace Live\Database;

use Live\Response;

class Fan extends Follow
{
    public $table_prefix = 'fan_';
    public $key = 'fan:';
    public $key_count = 'fan_n:';

    public $limit = 5000;

    /**
     * @param int $uid
     * @param int $follow_uid 被关注人的uid
     * @return bool
     */
    public function follow($uid, $follow_uid)
    {
        if ($uid == $follow_uid)
            return false;

        $limit = (new \Live\Database\User())->isVip($uid) ? 500 : 200;

        $ds_follow = new Follow();
        $count = $ds_follow->getCount($uid);
        if ($count >= $limit)
            return Response::msg('关注已达上限');

        $fan = $this->add($follow_uid, $uid);
        if ($fan)
            $follow = $ds_follow->add($uid, $follow_uid);

        return $fan;
    }

    public function unfollow($uid, $follow_uid)
    {
        if ($uid == $follow_uid)
            return false;

        $fan = $this->del($follow_uid, $uid);

        $follow = (new Follow())->del($uid, $follow_uid);

        return $fan;
    }

    public function isFollow($uid, $follow_uid)
    {
        $this->getList($follow_uid, 0, 1);
        return false !== $this->cache->link->zScore($this->key . $follow_uid, $uid);
    }
}