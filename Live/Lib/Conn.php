<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/26
 * Time: 下午2:40
 */

namespace Live\Lib;

use Live\Response;
use Live\Database\User;
use \Swoolet\App;

class Conn
{
    const TYPE_MESSAGE = 1;//普通消息
    const TYPE_HORN = 2;//广播喇叭
    const TYPE_FOLLOW = 3;//关注主播
    const TYPE_ENTER = 4;//进入房间
    const TYPE_PRAISE = 5;//点赞
    const TYPE_ROOM_BROADCAST = 6;//房间广播
    const TYPE_ROOM_ONE = 7;//只自己收到
    const TYPE_GIFT = 10;//送礼
    const TYPE_LIVE_STOP = 20;//停播

    public $fds;
    public $ids;
    public $rooms;

    public $sub;
    public $pub;
    public $key_conn = 'conn';

    static public $subscribe = false;

    public function __construct()
    {
        $this->sub = new \Swoolet\Data\RedisAsync('redis_async');
        $this->pub = new \Swoolet\Data\RedisAsync('redis_async');
        //$this->redis->debug = 1;
    }

    public function subscribe()
    {
        if (self::$subscribe)
            return;

        $this->sub->subscribe($this->key_conn, function ($data, $success) {
            //var_dump('subscribe', $data[2]);

            $data = \msgpack_unpack($data[2]);
            if (is_array($data) && $action = &$data['a']) {
                $this->{$action}($data);
            }

            /*

            if ($data = \msgpack_unpack($data[2]) && $action = &$data['action']){
                var_dump($data['action']);

                $this->{$action}($data);
            }
            */
        });

        self::$subscribe = true;
    }

    public function unsubscribe($fd)
    {
        $this->sub->unsubscribe($this->key_conn, function ($data, $success) {
        });
    }

    public function &getRoom($room_id)
    {
        return $this->rooms[$room_id];
    }

    public function &getInfo($fd)
    {
        return $this->fds[$fd];
    }

    public function &getFd($uid)
    {
        return $this->ids[$uid];
    }

    public function &getRoomUser($room_id, $uid)
    {
        return $this->rooms[$room_id][$uid];
    }

    public function join($fd, $uid, $room_id, $user = [])
    {
        $this->fds[$fd] = [$uid, $room_id, $user];
        $this->ids[$uid] = $fd;

        var_dump('fds', json_encode($this->fds));

        //加入房间
        if ($room_id) {
            //退出已经存在的房间
            $this->leaveRoom($fd);
            $room = &$this->getRoom($room_id) or $room = [];
            $room[$uid] = $fd;

            var_dump('room', json_encode($this->rooms));
        }
    }

    public function leave($fd)
    {
        $conn = $this->leaveRoom($fd);
        if ($conn) {
            $uid = $conn[0];
            unset($this->fds[$fd], $this->ids[$uid]);
        }
    }

    public function leaveRoom($fd)
    {
        $conn = $this->getInfo($fd);
        if ($conn) {
            list($uid, $room_id) = $conn;
            unset($this->rooms[$room_id][$uid]);
        }

        var_dump('leave_fds', json_encode($this->fds));
        var_dump('leave_room', json_encode($this->rooms));

        return $conn;
    }

    public function kickRoomUser($room_id, $uid)
    {
        if ($fd = $this->getRoomUser($room_id, $uid)) {
            $this->leave($fd);
        }
    }

    public function createRoom($fd, $uid)
    {
        $this->rooms[$uid] = [];

        $user = (new User())->getShowInfo($uid, 'simple');

        $this->join($fd, $uid, $uid, [
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'admin' => true,
        ]);
    }

    public function sendToRoom($room_id, $uid, $msg, $action = 'toRoom')
    {
        $msg = [
            'a' => $action,
            'room_id' => $room_id,
            'uid' => $uid,
            'msg' => $msg,
        ];

        $this->pub->publish($this->key_conn, \msgpack_pack($msg), function ($result, $success) {
            //var_dump('publish', $result, $success);
        });
    }

    public function sendToRoomUser($room_id, $to_uid, $msg)
    {
        $this->sendToRoom($room_id, $to_uid, $msg, 'toRoomUser');
    }

    public function updateAdmin($room_id, $admin_uid, $admin)
    {
        $this->sendToRoom($room_id, $admin_uid, $admin, 'pUpdateAdmin');
    }

    public function sendToUser($from_uid, $to_uid, $msg)
    {
        $msg = [
            'a' => 'toUser',
            'to' => $to_uid,
            'msg' => [
                'uid' => $from_uid,
                'msg' => $msg,
            ],
        ];

        $this->pub->publish($this->key_conn, \msgpack_pack($msg), function ($result, $success) {
            //var_dump('publish', $result, $success);
        });
    }

    protected function toRoom($data)
    {
        $room_id = $data['room_id'];
        $send_uid = $data['uid'];
        $msg = json_encode($data['msg'], \JSON_UNESCAPED_UNICODE);
        //var_dump($this->room, $send_fd);

        /**
         * @var \swoole_websocket_server $sw
         */
        $sw = App::$server->sw;
        foreach ($this->getRoom($room_id) as $uid => $fd) {
            if ($send_uid != $uid) {
                $sw->push($fd, $msg);
            }
        }
    }

    protected function toRoomUser($data)
    {
        $room_id = $data['room_id'];
        $to_uid = $data['uid'];
        $msg = json_encode($data['msg'], \JSON_UNESCAPED_UNICODE);
        //var_dump($this->room, $send_fd);

        $room = &$this->getRoom($room_id);
        if ($room && $fd = &$room[$to_uid]) {
            App::$server->sw->push($fd, $msg);
        }
    }

    /**
     * 更新admin
     * @param $data
     */
    protected function toUpdateAdmin($data)
    {
        $room_id = $data['room_id'];
        $uid = $data['uid'];
        $admin = $data['msg'];

        if ($fd = $this->getRoomUser($room_id, $uid)) {
            $this->fds[$fd][4] = $admin;
        }
    }

    protected function toUser($data)
    {
        $to = $data['to'];
        $msg = json_encode($data['msg'], \JSON_UNESCAPED_UNICODE);

        if ($fd = $this->getFd($to))
            App::$server->sw->push($fd, $msg);
    }
}