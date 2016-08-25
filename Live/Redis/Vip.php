<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午3:09
 */

namespace Live\Redis;


class Vip extends Common
{
    public $cfg_key = 'redis_1';

    public $key_wait = 'vip_award_wait';
    public $key_award = 'vip_award_list';

    public $key_award_limit = 'vip_award_limit:';

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

    public function addAward($uid, $score)
    {
        if ($score < 30)
            return 0;

        return $this->link->rPush($this->key_award, \msgpack_pack([$uid, $score]));
    }

    public function getAward($start = 0, $limit)
    {
        return $this->link->lRange($this->key_award, $start, $limit - 1);
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