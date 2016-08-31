<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Database\Fan;
use Live\Database\Income;
use Live\Database\RoomMsg;
use Live\Redis\Rank;
use Live\Response;

class Replay extends Basic
{
    public function view()
    {
        $data = parent::getValidator()->required('token')->required('uid')->required('id')->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];
        $room_id = $uid = $data['uid'];
        $id = $data['id'];

        $replay = (new \Live\Database\Replay())->get($uid, $id);

        Response::data([
            'replay' => $replay,
            'user' => (new \Live\Database\User())->getShowInfo($uid, 'lv'),
            'rank' => (new Rank())->getRankInRoom($room_id, 0),
            'is_follow' => (new Fan())->isFollow($token_uid, $room_id),
            'money' => (new Income())->getIncome($room_id),
        ]);
    }

    public function getRoomMsg()
    {
        $data = parent::getValidator()->required('token')->required('uid')->required('id')->required('ts')->getResult();
        if (!$data)
            return $data;

        $room_id = $uid = $data['uid'];
        $id = $data['id'];

        $replay = (new \Live\Database\Replay())->get($uid, $id);

        if ($data['ts'] > 0) {
            $start_ts = $data['ts'];
        } else {
            $start_ts = $replay['create_ts'];
        }

        $end_ts = $replay['create_ts'] + $replay['duration'];

        $room_msg = (new RoomMsg())->getByTS($room_id, $start_ts, $end_ts);

        $ds_user = new \Live\Database\User();
        foreach ($room_msg as &$row) {
            $row['user'] = $ds_user->getShowInfo($row['from_uid'], 'lv');
            unset($row['from_uid']);

            $row['ts'] -= $start_ts;
        }

        Response::data(['room_msg' => $room_msg]);
    }
}