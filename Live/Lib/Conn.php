<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/26
 * Time: 下午2:40
 */

namespace Live\Lib;

use Live\Database\RoomAdmin;
use Live\Database\User;
use \Swoolet\App;
use Swoolet\Lib\File;

class Conn
{
    const TYPE_MESSAGE = 1;//普通消息
    const TYPE_HORN = 2;//广播喇叭
    const TYPE_FOLLOW = 3;//关注主播
    const TYPE_ENTER = 4;//进入房间
    const TYPE_PRAISE = 5;//点赞
    const TYPE_ROOM_BROADCAST = 6;//房间广播
    const TYPE_ROOM_ONE = 7;//房间,只自己收到
    const TYPE_CHAT = 8;//私信

    const TYPE_GIFT = 10;//送礼
    const TYPE_LIVE_STOP = 20;//停播

    public $uid;
    public $conn;
    public $room;

    public $sub;
    public $pub;
    public $key_room_chat = 'r_chat:';
    public $key_user_chat = 'u_chat:';

    static public $subscribe = false;

    public function __construct()
    {
        $this->sub = new \Swoolet\Data\RedisAsync('redis_async');
        $this->pub = new \Swoolet\Data\RedisAsync('redis_async');
        //$this->redis->debug = 1;
    }

    public function &getFd($uid)
    {
        return $this->uid[$uid];
    }

    public function &getConn($fd)
    {
        return $this->conn[$fd];
    }

    public function &getRoom($room_id)
    {
        $room = &$this->room[$room_id] or $room = [];

        return $room;
    }

    public function join($fd, $uid)
    {
        $this->uid[$uid] = $fd;

        $this->subscribe($this->key_user_chat . $uid, $uid);

        $this->joinRoom($fd, 0, $uid, []);
    }

    public function leave($fd)
    {
        $conn = $this->getConn($fd);
        if ($conn) {
            $uid = $conn[0];
            $user_fd = $this->getFd($uid);

            //var_dump('conn', $conn, 'uid', $uid, 'user_fd', $user_fd, 'fd', $fd);
            if ($user_fd == $fd) {
                unset($this->conn[$fd], $this->uid[$uid]);
                $this->unsubscribe($this->key_user_chat . $uid);
            }
        }
    }

    public function joinRoom($fd, $room_id, $uid, $user)
    {
        $this->conn[$fd] = [$uid, $room_id, $user];

        //退出已经存在的房间
        if ($room_id) {
            $this->leaveRoom($fd);

            $this->room[$room_id][$uid] = $fd;

            //$this->subscribe($this->key_room_chat . $room_id, $uid, $fd);
        }
    }

    public function leaveRoom($fd)
    {
        $conn = $this->getConn($fd);
        if ($conn) {
            list($uid, $room_id) = $conn;
            unset($this->room[$room_id][$uid]);
        }

        return $conn;
    }

    public function subRoom()
    {
        if (!self::$subscribe) {
            //var_dump(self::$subscribe);
            $this->subscribe($this->key_room_chat);
            self::$subscribe = true;
        }

        return $this;
    }

    public function subscribe($key, $uid = 0)
    {
        $this->sub->subscribe($key, function ($data, $err) use ($uid) {
            if ($err)
                return;

            $data = \msgpack_unpack($data[2]);
            if ($data && is_array($data))
                $this->msgAction($data, $uid);
        });
    }

    public function msgAction($data, $sub_uid)
    {
        /**
         * @var \swoole_websocket_server $sw
         */
        $sw = App::$server->sw;

        $a = &$data['a'];
        if ($a == 'toRoom') {
            //$send_uid != $uid
            var_dump('room', $this->getRoom($data['room_id']));
            foreach ($this->getRoom($data['room_id']) as $uid => $fd) {
                if ($data['uid'] != $uid && !$sw->push($fd, $data['msg'])) {
                    unset($this->room[$data['room_id']][$uid]);
                }
            }
        } elseif ($a == 'toUser') {
            $sw->push($this->getFd($sub_uid), $data['msg']);

        } elseif ($a == 'updateAdmin') {

            $fd = $this->getFd($data['uid']);
            if ($fd && ($conn = $this->getConn($fd)) && $conn[0] == $data['uid']) {
                $admin = $data['msg'];
                $this->conn[$fd][4] = $admin;
            }
        }
    }

    public function unsubscribe($key)
    {
        $this->sub->unsubscribe($key, function ($data, $err) {
        });
    }

    public function createRoom($fd, $uid)
    {
        $this->rooms[$uid] = [];

        $user = (new User())->getShowInfo($uid, 'simple');

        $this->joinRoom($fd, $uid, $uid, [
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'admin' => false,
        ]);
    }

    public function sendToRoom($room_id, $uid, $msg, $action = 'toRoom')
    {
        $msg = [
            'a' => $action,
            'room_id' => $room_id,
            'uid' => $uid,
            'msg' => json_encode($msg, \JSON_UNESCAPED_UNICODE),
        ];

        $this->pub->publish($this->key_room_chat, \msgpack_pack($msg), function ($result, $err) {
            //var_dump('publish', $result);
        });
    }

    public function updateAdmin($room_id, $admin_uid, $admin)
    {
        $this->sendToRoom($room_id, $admin_uid, $admin, 'updateAdmin');
    }

    public function sendToUser($to_uid, $msg)
    {
        $msg = [
            'a' => 'toUser',
            'uid' => $to_uid,
            'msg' => json_encode($msg, \JSON_UNESCAPED_UNICODE),
        ];

        $this->pub->publish($this->key_user_chat . $to_uid, \msgpack_pack($msg), function ($result, $err) {
            //var_dump('publish', $result);
        });
    }

    public function onWorkerStart($sw, $worker_id)
    {
        $filename = "/tmp/worker_{$worker_id}.php";
        $arr = File::get($filename, true);
        if ($arr) {
            list($this->uid, $this->conn, $this->room) = $arr;

            //var_dump('start', $this->room);

            //重新监听个人聊天
            foreach ($this->uid as $uid => $fd) {
                $this->subscribe($this->key_user_chat . $uid, $uid);
            }

            File::rm($filename);
        }

        return $this;
    }

    public function onWorkerStop($sw, $worker_id)
    {
        $data = [
            $this->uid,
            $this->conn,
            $this->room,
        ];

        $this->unsubscribe($this->key_room_chat);

//        foreach ($this->uid as $uid => $fd) {
//            $this->unsubscribe($this->key_user_chat . $uid);
//        }

        //var_dump('stop', $this->room);
        File::touch("/tmp/worker_{$worker_id}.php", $data, true);

        return $this;
    }
}