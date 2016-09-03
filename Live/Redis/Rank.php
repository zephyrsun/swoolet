<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午3:09
 */

namespace Live\Redis;


class Rank extends Common
{
    public $cfg_key = 'redis_1';

    public $key_rank_send = 'rank_send';
    public $key_rank_income = 'rank_income';

    public $key_rank_room = 'rank_room:';
    public $key_room_user_num = 'room_user_num:';

    public $limit = 30;

    public function addRank($send_uid, $to_uid, $n)
    {
        //房间土豪榜
        $score = $this->link->zIncrBy($this->key_rank_room . $to_uid, $n, $send_uid);
        //土豪总榜
        //$this->link->zIncrBy($this->key_rank_send, $n, $send_uid);
        //收礼总榜
        //$this->link->zIncrBy($this->key_rank_income, $n, $to_uid);

        return $score;
    }

    public function joinRoom($room_id, $uid)
    {
        $limit = $this->limit;
        $key = $this->key_rank_room . $room_id;

        $n = $this->link->zCard($key);

        if ($n == 0) {
            //月榜
            $this->link->expireAt($key, \strtotime('first day of next month 00:00', \Swoolet\App::$ts));
        } elseif ($n > $limit * 2) {
            //移除超过限制的
            $this->link->zRemRangeByRank($key, $limit + 1, -1);
        }

        $this->link->zIncrBy($key, 0, $uid);

        $this->incrRoomUserNum($room_id);

        return $n;
    }

    public function incrRoomUserNum($room_id)
    {
        $key = $this->key_room_user_num . $room_id;
        $n = $this->link->incr($key);
        if ($n == 1) {
            $this->link->expire($key, 3600 * 6);
        }

        return $n;
    }

    public function getRoomUserNum($room_id)
    {
        return (int)$this->link->get($this->key_room_user_num . $room_id);
    }

    public function getUserRankInRoom($room_id, $uid)
    {
        $rank = $this->link->zRevRank($this->key_rank_room . $room_id, $uid);
        if (!$rank || $rank > $this->limit)
            return -1;

        return $rank;
    }

    public function getRankInRoom($uid, $start)
    {
        return $this->_getRank($this->key_rank_room . $uid, $start);
    }

    public function getRankOfSend($start)
    {
        return $this->_getRank($this->key_rank_send, $start);
    }

    public function getRankOfIncome($start)
    {
        return $this->_getRank($this->key_rank_income, $start);
    }

    private function _getRank($key, $start)
    {
        $data = parent::revRange($key, $start, $this->limit, true);

        $ret = array();
        $db_user = new \Live\Database\User();
        foreach ($data as $uid => $money) {

            $user = $db_user->getShowInfo($uid, 'lv');
            $user['money'] = $money;

            $ret[] = $user;
        }

        return $ret;
    }
}