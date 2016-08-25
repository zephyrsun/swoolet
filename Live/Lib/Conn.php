<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/26
 * Time: 下午2:40
 */

namespace Live\Lib;

use Swoolet\App;
use Swoolet\Data\RedisAsync;
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
    public $sub_user;

    public $key_room_chat = 'r_chat:';
    public $key_user_chat = 'u_chat:';

    static public $subscribe = false;

    public function __construct()
    {
        $this->sub = new RedisAsync('redis_async', 'sub');
        $this->pub = new RedisAsync('redis_async', 'pub');
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

        $this->subUser($uid);

        $this->joinRoom($fd, 0, $uid, []);
    }

    public function leave($fd)
    {
        $conn = $this->getConn($fd);
        if ($conn && ($uid = $conn[0]) && $this->getFd($uid) == $fd) {
            unset($this->conn[$fd], $this->uid[$uid]);

            RedisAsync::release($uid, $this->key_user_chat . $uid);
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

    public function subUser($uid)
    {
        $sub = new RedisAsync('redis_async', $uid);
        $sub->subscribe($this->key_user_chat . $uid, function ($data, $err) use ($uid) {
            if ($err)
                return;

            $this->msgAction($data, $uid);
        });
    }

    public function subRoom()
    {
        $this->sub->subscribe($this->key_room_chat, function ($data, $err) {
            if ($err)
                return;

            $this->msgAction($data);
        });

        return $this;
    }

    public function msgAction($data, $sub_uid = 0)
    {
        $data = \msgpack_unpack($data[2]);
        if (!$data || !is_array($data))
            return;

        /**
         * @var \swoole_websocket_server $sw
         */
        $sw = App::$server->sw;

        $a = &$data['a'];
        if ($a == 'toRoom') {
            //$send_uid != $uid
            //var_dump('room', $this->getRoom($data['room_id']));
            foreach ($this->getRoom($data['room_id']) as $uid => $fd) {
                if ($data['uid'] != $uid && !$sw->push($fd, $data['msg'])) {
                    unset($this->room[$data['room_id']][$uid]);
                }
            }
        } elseif ($a == 'toUser') {
            if ($fd = $this->getFd($sub_uid)) {
                //var_dump($sub_uid, $fd);
                $sw->push($fd, $data['msg']);
            }
        } elseif ($a == 'updateUser') {
            if ($fd = $this->getFd($sub_uid)) {
                $this->conn[$fd][2] = $data['user'] + $this->conn[$fd][2];
            }
        }
    }

    public function createRoom($fd, $uid, $user)
    {
        $this->room[$uid] = [];

        $this->joinRoom($fd, $uid, $uid, $user);
    }

    public function sendToRoom($room_id, $uid, $msg)
    {
        $msg = [
            'a' => 'toRoom',
            'room_id' => $room_id,
            'uid' => $uid,
            'msg' => json_encode($msg, \JSON_UNESCAPED_UNICODE),
        ];

        $this->pub->publish($this->key_room_chat, \msgpack_pack($msg), function ($result, $err) {
            //var_dump('sendToRoom', $result);
        });
    }

    public function updateUser($uid, $user)
    {
        $msg = [
            'a' => 'updateUser',
            'uid' => $uid,
            'user' => $user,
        ];

        $this->pub->publish($this->key_user_chat . $uid, \msgpack_pack($msg), function ($result, $err) {
            var_dump('updateUser', $result);
        });
    }

    public function sendToUser($uid, $msg, $chat = false)
    {
        $msg = [
            'a' => 'toUser',
            'uid' => $uid,
            'msg' => json_encode($msg, \JSON_UNESCAPED_UNICODE),
        ];

        $this->pub->publish($this->key_user_chat . $uid, \msgpack_pack($msg), function ($result, $err) use ($chat) {
            //var_dump('sendToUser', $result);
            if ($chat && $result == 0) {
                //发送未成功

            }
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
            if ($this->uid) {
                foreach ($this->uid as $uid => $fd) {
                    $this->subUser($uid);
                }
            }

            File::rm($filename);
        }

        //$this->subRoom();

        return $this;
    }

    public function onWorkerStop($sw, $worker_id)
    {
        $data = [
            $this->uid,
            $this->conn,
            $this->room,
        ];

        //var_dump('stop', $this->room);

        File::touch("/tmp/worker_{$worker_id}.php", $data, true);

        RedisAsync::release('sub', $this->key_room_chat);
        RedisAsync::release('pub');

        return $this;
    }
}