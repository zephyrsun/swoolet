<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/26
 * Time: 下午2:40
 */

namespace Live\Lib;

use Live\Database\RoomMsg;
use Live\Redis\RedisPub;

class Conn
{
    const TYPE_MESSAGE = 1;//普通消息
    const TYPE_HORN = 2;//广播喇叭
    const TYPE_FOLLOW = 3;//关注主播
    const TYPE_ENTER = 4;//进入房间
    const TYPE_PRAISE = 5;//点赞
    const TYPE_ROOM_BROADCAST = 6;//房间广播,系统消息
    const TYPE_ROOM_ONE = 7;//房间,只自己收到
    const TYPE_CHAT = 8;//私信

    const TYPE_GIFT = 10;//送礼
    const TYPE_LIVE_STOP = 20;//停播

    const TYPE_OFFLINE_CHAT_MSG = 'offlineMsg';

    const ROOM_CHAT = 'room_chat:';
    const USER_CHAT = 'user_chat:';

    public $pub;

    public $room_msg;

    public $user_store;

    /**
     * @var \swoole_process $process
     */
    public $process;

    public function __construct()
    {
        $this->user_store = ConnStore::userStore();

        $this->pub = new RedisPub();

        $this->room_msg = new RoomMsg();
    }

    static public function getInstance()
    {
        static $ins;

        $ins or $ins = new Conn();

        return $ins;
    }

    /**
     * @param \swoole_server $sw
     */
    public function process($sw)
    {
        $this->process = new \swoole_process(function (\swoole_process $process) use ($sw) {

            $conn_store = new ConnStore($this, $sw);

            $conn_store->subRoom();

            swoole_event_add($process->pipe, function ($pipe) use ($conn_store, $process) {
                $data = explode('|', $process->read());

                $cmd = array_shift($data);

                //$conn_store->{$cmd}($data);
                call_user_func_array([$conn_store, $cmd], $data);
            });
        });

        $sw->addProcess($this->process);

        return $this;
    }

    public function join($fd, $uid, $user)
    {
        $this->joinRoom($fd, 0, $uid, $user);

        $this->process->write("join|$fd|$uid");
    }

    public function leave($fd, $pause = 0)
    {
        $this->process->write("leave|$fd|$pause");
    }

    public function joinRoom($fd, $room_id, $uid, $user)
    {
        //退出已经存在的房间
        if ($room_id) {
            $this->process->write("joinRoom|$fd|$room_id|$uid");
        }

        $this->user_store->set($fd, [
            'fd' => $fd,
            'room_id' => $room_id,
            'uid' => $user['uid'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'lv' => $user['lv'],
            'is_vip' => $user['is_vip'],
            'is_tycoon' => $user['is_tycoon'],
        ]);
    }

    public function leaveRoom($fd)
    {
        $this->process->write("leaveRoom|$fd");
    }

    public function updateUser($uid, $user)
    {
        $msg = [
            'a' => 'updateUser',
            'uid' => $uid,
            'user' => $user,
        ];

        $this->pub->publish(self::USER_CHAT . $uid, \msgpack_pack($msg));
    }

    public function sendToUser($uid, $msg, callable $cb = null)
    {
        $data = [
            'a' => 'toUser',
            'uid' => $uid,
            'msg' => json_encode($msg, \JSON_UNESCAPED_UNICODE),
        ];

        $int = $this->pub->publish(self::USER_CHAT . $uid, \msgpack_pack($data));
        var_dump('sendToUser', $int);
        $cb && $cb($int);
    }

    public function sendToRoom($room_id, $uid, $msg, $ext = '')
    {
        $json_msg = json_encode($msg, \JSON_UNESCAPED_UNICODE);

        $msg = [
            'a' => 'toRoom',
            'ext' => $ext,
            'room_id' => $room_id,
            'uid' => $uid,
            'msg' => $json_msg,
        ];

        $this->pub->publish(self::ROOM_CHAT, \msgpack_pack($msg));

        // save msg
        $this->room_msg->save($room_id, $uid, $json_msg, \Swoolet\App::$ts);
    }

    public function createRoom($fd, $uid, $user)
    {
        $this->joinRoom($fd, $uid, $uid, $user);
    }

    public function stopRoom($room_id, $uid)
    {
        $this->sendToRoom($room_id, $uid, [
            't' => Conn::TYPE_LIVE_STOP,
            'msg' => '直播结束',
        ], 'stop');
    }

    public function getConn($fd)
    {
        if ($user = $this->user_store->get($fd)) {
            $room_id = $user['room_id'];

            unset($user['fd'], $user['room_id']);

            return [$room_id, $user];
        }

        return $user;
    }
}