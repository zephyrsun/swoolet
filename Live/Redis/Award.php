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

    public $key_wait = 'award_wait';
    public $key_award = 'award_list';

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
        // $score = substr(microtime(true) * 1e4, 4);
        $score = \Swoolet\App::$ts . $uid;

        return $this->link->zAdd($this->key_award, $score, $msg);
    }

    public function getRecommend($start, $limit = 30)
    {
        return parent::revRange($this->key_award, $start, $limit, true);
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