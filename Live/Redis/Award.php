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

    public $key_wait = 'award_wait';
    public $key_award = 'award_list';
    public $key_award_msg = 'award_list_msg';

    public $key_award_limit = 'award_limit:';

    public function addWait($uid, $val)
    {
        $val = (int)($val / 10);//相当于返现10%

        return $this->link->hSet($this->key_wait, $uid, $val);
    }

    public function getWait($uid)
    {
        return $this->link->hGet($this->key_wait, $uid);
    }

    public function decrWait($uid, $int)
    {
        return $this->link->hIncrBy($this->key_wait, $uid, -$int);
    }

    public function delWait($uid)
    {
        return $this->link->hDel($this->key_wait, $uid);
    }

    /**
     * @param $uid
     * @param $msg
     * @return int
     */
    public function addRecommend($uid, $msg)
    {
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