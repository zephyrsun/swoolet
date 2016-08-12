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
    const TYPE_SYS_MESSAGE = 6;//系统消息
    const TYPE_GIFT = 10;//送礼
    const TYPE_LIVE_STOP = 20;//停播

    public $ids;
    public $room;
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
            if (is_array($data) && $action = &$data['action']) {
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
        $room = &$this->room[$room_id] or $room = [];
        return $room;
    }

    public function joinRoom($fd, $uid, $room_id, $nickname, $avatar)
    {
        $room = &$this->getRoom($room_id);

        //退出已经存在的房间
        $this->quitConn($fd);

        //保存链接
        $this->ids[$fd] = [$uid, $room_id, $nickname, $avatar];

        //加入房间
        $room[$uid] = $fd;

        //var_dump('joinRoom', $this->room);

        return $room;
    }

    public function &getConn($fd)
    {
        return $this->ids[$fd];
    }

    public function quitConn($fd)
    {
        $conn = $this->getConn($fd);
        if ($conn) {
            list($uid, $room_id) = $conn;
            unset($this->room[$room_id][$uid], $this->ids[$fd]);
        }

        //var_dump('quitConn', $this->room);

        return $conn;
    }

    public function createRoom($fd, $uid)
    {
        $this->room[$uid] = [];

        $user = (new User())->getShowInfo($uid, 'simple');

        $this->joinRoom($fd, $uid, $uid, $user['nickname'], $user['avatar']);
    }

    public function destroyRoom($room_id, $fd)
    {
        $this->roomMsg($room_id, $fd, [
            't' => Conn::TYPE_LIVE_STOP,
            'msg' => '直播结束',
        ]);
    }

    public function roomMsg($room_id, $uid, $msg)
    {
        $msg = [
            'action' => 'push',
            'room_id' => $room_id,
            'uid' => $uid,
            'msg' => $msg,
        ];

        $this->pub->publish($this->key_conn, \msgpack_pack($msg), function ($result, $success) {
            //var_dump('publish', $result, $success);
        });
    }

    public function push($data)
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
            if ($send_uid != $uid)
                $sw->push($fd, $msg);
        }
    }
}