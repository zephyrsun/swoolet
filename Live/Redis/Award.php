<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午3:09
 */

namespace Live\Redis;


class Award extends Common
{
    public $cfg_key = 'redis_store';
    public $db_index = 1;

    public $key_vip = 'award_vip';
    public $key_award = 'award_list';
    public $key_award_msg = 'award_list_msg';

    public $key_award_limit = 'award_limit:';

    public function addVip($uid, $val)
    {
        $this->link->rPush($this->key_vip, $uid);
    }

    public function getVip($n = 3)
    {
        $key = $this->key_vip;
        $limit = 500;

        $len = $this->link->lLen($key);
        $start = mt_rand(0, $len - $n);
        $ret = $this->link->lRange($key, $start, $start + $n - 1);

        if ($len > $limit * 2) {
            $this->link->lTrim($key, $len - $limit, -1);
        }

        return $ret;
    }

    /**
     * @param $uid
     * @param $msg
     * @return int
     */
    public function addRecommend($uid, $msg)
    {
        return;

        $key = $this->key_award;
        $key_msg = $this->key_award_msg;
        $limit = 50;

        $score = \Swoolet\App::$ts;
        $ret = $this->link->zAdd($this->key_award, $score, $uid);

        $n = $this->link->zCard($key);
        if ($n > $limit * 1.5) {
            //移除超过限制的
            $data = $this->link->zRevRange($key, $limit - 1, -1);
            $this->link->zRemRangeByRank($key, 0, $n - $limit - 1);

            array_unshift($data, $key_msg);
            call_user_func_array([$this->link, 'hDel'], $data);
        } else {
            $ret = $this->link->hSet($key_msg, $uid, $msg);
        }

        return $ret;
    }

    public function getRecommend($start, $limit = 30)
    {
        $list = parent::revRange($this->key_award, $start, $limit, false);
        $all_msg = $this->link->hMGet($this->key_award_msg, $list);

        $ds_user = new \Live\Database\User();
        $ret = [];
        foreach ($list as &$uid) {
            $user = $ds_user->getShowInfo($uid, 'simple');
            if ($user) {
                $msg = &$all_msg[$uid] or $msg = '';
                $user['msg'] = $msg;

                $ret = $user;
            }
        }

        return $ret;
    }

    public function couldAward($uid)
    {
        $key = $this->key_award_limit . $uid;
        $ret = $this->link->setnx($key, 1);
        if ($ret)
            $this->link->expireAt($key, \strtotime('+1 day midnight', \Swoolet\App::$ts));

        return $ret;
        //  return parent::set(, 1, );
    }
}