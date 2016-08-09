<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午3:09
 */

namespace Live\Redis;


class RoomAdmin extends Common
{
    public $cfg_key = 'redis_1';

    public $key_silence = 'silence_';

    public function silenceUser($uid, $room_id)
    {
        return $this->link->set($this->key_silence . "{$uid}_{$room_id}", 1, 7200);
    }

    public function isSilence($uid, $room_id)
    {
        return $this->link->get($this->key_silence . "{$uid}_{$room_id}");
    }
}