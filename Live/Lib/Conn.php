<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/26
 * Time: 下午2:40
 */

namespace Live\Lib;

use Live\Response;
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

    /**
     * @var Conn $ins
     */
    static public $ins;

    /**
     * @return Conn
     */
    static public function getInstance()
    {
        self::$ins or self::$ins = new Conn();

        return self::$ins;
    }

    public function &getConn($fd)
    {
        $conn = &$this->ids[$fd] or $conn = [];
        return $conn;
    }

    public function quitConn($fd)
    {
        $conn = $this->getConn($fd);
        if ($conn) {
            $this->quitRoom($fd, $conn[1]);
            unset($this->ids[$fd]);
        }

        return $this;
    }

    public function &getRoom($room_id)
    {
        $room = &$this->room[$room_id] or $room = [];
        return $room;
    }

    public function enterRoom($fd, $uid, $room_id)
    {
        $room = &$this->getRoom($room_id);
        //if (!$room)
        //    return Response::msg('直播已结束', 1024);

        //退出已经存在的房间
        $this->quitConn($fd);

        //保存链接
        $this->ids[$fd] = [$uid, $room_id];

        //加入房间
        $room[$fd] = $uid;

        return $room;
    }

    public function quitRoom($fd, $room_id)
    {
        $room = &$this->getRoom($room_id);
        unset($room[$fd]);
    }

    public function createRoom($fd, $uid)
    {
        $this->room[$uid] = [];

        $this->enterRoom($fd, $uid, $uid);
    }

    public function destroyRoom($room_id)
    {
        $room = $this->getRoom($room_id);

        $msg = [
            't' => Conn::TYPE_LIVE_STOP,
            'msg' => '直播结束',
        ];

        /**
         * @var \swoole_websocket_server $sw
         */
        $sw = App::$server->sw;
        foreach ($room as $fd => $data) {
            $sw->push($fd, json_encode($msg, \JSON_UNESCAPED_UNICODE));
            $sw->close($fd);
        }
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