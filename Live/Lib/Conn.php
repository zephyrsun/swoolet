<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/26
 * Time: 下午2:40
 */

namespace Live\Lib;

use \Swoolet\App;

class Conn
{
    const TYPE_MESSAGE = 1;//普通消息
    const TYPE_HORN = 2;//广播喇叭
    const TYPE_FOLLOW = 3;//关注主播
    const TYPE_ENTER = 4;//进入房间
    const TYPE_PRAISE = 5;//点赞
    const TYPE_GIFT = 10;//送礼

    public $conn;

    public function &getConn($fd)
    {
        $conn = &$this->conn[$fd] or $conn = [];
        return $conn;
    }

    public function quitConn($fd)
    {
        $conn = $this->getConn($fd);
        if ($conn)
            $this->quitRoom($fd, $conn[1]);

        return $this;
    }

    public function &getRoom($room_id)
    {
        $room = &$this->{$room_id} or $room = [];
        return $room;
    }

    public function enterRoom($fd, $uid, $room_id)
    {
        $room = &$this->getRoom($room_id);

        //退出已经存在的房间
        $this->quitConn($fd);

        //保存链接
        $this->conn[$fd] = [$uid, $room_id];

        //加入房间
        $room[$fd] = $uid;

        return $room;
    }

    public function quitRoom($fd, $room_id)
    {
        $room = &$this->getRoom($room_id);
        unset($room[$fd]);
    }

    public function broadcast($room_id, $msg)
    {
        /**
         * @var \swoole_websocket_server $sw
         */
        $sw = App::$server->sw;
        foreach ($this->getRoom($room_id) as $fd => $data) {
            yield $sw->push($fd, json_encode($msg, \JSON_UNESCAPED_UNICODE));
        }
    }
}