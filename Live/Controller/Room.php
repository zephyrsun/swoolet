<?php

namespace Live\Controller;

use Live\Response;
use Swoolet\App;

class Room extends Basic
{
    public $conn;

    public function __construct()
    {
        $this->conn = new RoomConn();
    }

    public function init($request)
    {
        parent::init($request);

        $this->conn->request = $request;
    }

    public function enter()
    {
        $data = \Live\getParams(function ($v) {
            /**
             * @var \Swoolet\Lib\Validator $v
             */
            $v->ge('uid', 1)->ge('room_id', 1);
        });

        if (!$data)
            return;

        $room_id = $data['room_id'];
        $uid = $data['uid'];

        $this->conn->broadcast($room_id, [
            't' => RoomConn::TYPE_ENTER,
            'uid' => $uid,
            'nickname' => "nickname{$uid}",
        ]);

        $this->conn->setRoom($room_id, $uid);

        Response::msg('登陆成功');
        //$this->room[$data['room_id']][$this->request->fd] = $data['uid'];
    }
}

class RoomConn
{
    const TYPE_MESSAGE = 1;//普通消息
    const TYPE_HORN = 2;//广播喇叭
    const TYPE_FOLLOW = 3;//关注主播
    const TYPE_ENTER = 4;//进入房间
    const TYPE_PRAISE = 5;//点赞
    const TYPE_GIFT = 10;//送礼
    /**
     * @var \swoole_websocket_frame $request
     */
    public $request;

    public function &getRoom($room_id)
    {
        $room = &$this->{$room_id} or $room = array();
        return $room;
    }

    public function setRoom($room_id, $data)
    {
        $room = &$this->getRoom($room_id);

        $room[$this->request->fd] = $data;

        return $room;
    }

    public function broadcast($room_id, $msg)
    {
        /**
         * @var \swoole_websocket_server $sw
         */
        $sw = App::$server->sw;
        foreach ($this->getRoom($room_id) as $fd => $data) {
            $sw->push($fd, json_encode($msg, \JSON_UNESCAPED_UNICODE));
        }
    }
}