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

class Conn2
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

    public $info;

    public $sub;
    public $pub;
    public $key_conn = 'conn';
    public $key_room_chat = 'r_chat:';
    public $key_user_chat = 'u_chat:';

    static public $subscribe = false;

    public function __construct()
    {
        $this->sub = new \Swoolet\Data\RedisAsync('redis_async');
        $this->pub = new \Swoolet\Data\RedisAsync('redis_async');
        //$this->redis->debug = 1;
    }

    public function getUid($fd)
    {
        $conn = App::$server->sw->connection_info($fd);
        if ($conn && $uid = &$conn['uid'])
            return $uid;

        return 0;
    }

    public function &getConn($fd)
    {
        if (!$ret = &$this->info[$fd] && $uid = $this->getUid($fd)) {
            $user = (new User())->getUser($uid);

            $ret = [$uid, 0, [
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar'],
                'admin' => false,
            ]];

            var_dump($ret);
        }

        return $ret;
    }

    public function join($fd, $uid)
    {
        //App::$server->sw->bind($fd, $uid);
        $this->subscribe($this->key_user_chat . $uid, $uid, $fd);
    }

    public function leave($fd)
    {
        if ($uid = $this->getUid($fd))
            $this->unsubscribe($this->key_user_chat . $uid);
    }

    public function joinRoom($fd, $room_id, $uid, $user)
    {
        //退出已经存在的房间
        $this->leaveRoom($fd);

        $this->info[$fd] = [$uid, $room_id, $user];
        $this->subscribe($this->key_room_chat . $room_id, $uid, $fd);
    }

    public function leaveRoom($fd)
    {
        if ($room_id = &$this->rooms[$fd][1])
            $this->unsubscribe($this->key_room_chat . $room_id);

        unset($this->rooms[$fd]);
    }

    public function subscribe($key, $uid, $fd)
    {
        $this->sub->subscribe($key, function ($data, $err) use ($uid, $fd) {
            if ($err)
                return;

            $data = \msgpack_unpack($data[2]);
            if ($data && is_array($data))
                $this->msgAction($data, $uid, $fd);
        });
    }

    public function msgAction($data, $uid, $fd)
    {
        /**
         * @var \swoole_websocket_server $sw
         */
        $sw = App::$server->sw;

        $a = &$data['a'];
        var_dump($a, $data);
        if ($a == 'toRoom') {
            //$send_uid != $uid
            if ($data['uid'] != $uid)
                $sw->push($fd, $data['msg']);
        } elseif ($a == 'toUser') {
            // if ($data['uid'] == $uid)
            $sw->push($fd, $data['msg']);
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
            'admin' => true,
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

        $this->pub->publish($this->key_room_chat . $room_id, \msgpack_pack($msg), function ($result, $err) {
            //var_dump('publish', $result);
        });
    }

    public function updateAdmin($room_id, $admin_uid, $admin)
    {
        $this->sendToRoom($room_id, $admin_uid, $admin, 'toUpdateAdmin');
    }

    public function sendToUser($to_uid, $msg)
    {
        $msg = [
            'a' => 'toUser',
            'uid' => $to_uid,
            'msg' => json_encode($msg, \JSON_UNESCAPED_UNICODE),
        ];

        $this->pub->publish($this->key_user_chat . $to_uid, \msgpack_pack($msg), function ($result, $err) {
            var_dump('publish', $result);
        });
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
}