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


    public function addRank($send_uid, $to_uid, $n)
    {
        //房间土豪榜
        $this->link->zIncrBy($this->key_rank_room . $to_uid, $n, $send_uid);
        //土豪总榜
        $this->link->zIncrBy($this->key_rank_send, $n, $send_uid);
        //收礼总榜
        $this->link->zIncrBy($this->key_rank_income, $n, $to_uid);
    }

    public function joinRoom($room_id, $uid)
    {
        $limit = 10;
        $key = $this->key_rank_room . $room_id;

        $n = $this->link->zCard($key);
        if ($n < $limit) {

            //每周5点清空
            if ($n == 0) {
                $this->link->expireAt(\strtotime('next monday') + 18000);
            }

            $this->link->zIncrBy($key, 0, $uid);
        }

        $this->incrRoomUserNum($room_id);

        return $n;
    }

    public function incrRoomUserNum($room_id)
    {
        $key = $this->key_room_user_num . $room_id;
        $n = $this->link->incr($key);
        if ($n == 1) {
            $this->link->expire($key, 3600 * 5);
        }

        return $n;
    }

    public function getRoomUserNum($room_id)
    {
        return (int)$this->link->get($this->key_room_user_num . $room_id);
    }

    public function getRankInRoom($uid, $start)
    {
        return $this->_getRank($this->key_rank_room . $uid, $start, 'room');
    }

    public function getRankOfSend($start)
    {
        return $this->_getRank($this->key_rank_send, $start);
    }

    public function getRankOfIncome($start)
    {
        return $this->_getRank($this->key_rank_income, $start);
    }

    private function _getRank($key, $start, $type = '')
    {
        $data = parent::revRange($key, $start, 20, true);

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