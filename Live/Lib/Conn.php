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

    public function &getRoom($room_id)
    {
        $room = &$this->room[$room_id] or $room = [];
        return $room;
    }

    public function enterRoom($fd, $uid, $room_id, $nickname, $avatar)
    {
        $room = &$this->getRoom($room_id);

        //退出已经存在的房间
        $this->quitConn($fd);

        //保存链接
        $this->ids[$fd] = [$uid, $room_id, $nickname, $avatar];

        //加入房间
        $room[$uid] = $fd;

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

        return $conn;
    }

    public function createRoom($fd, $uid)
    {
        $this->room[$uid] = [];

        $user = (new User())->getShowInfo($uid, 'simple');

        $this->enterRoom($fd, $uid, $uid, $user['nickname'], $user['avatar']);
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
        foreach ($room as $uid => $fd) {
            $sw->push($fd, json_encode($msg, \JSON_UNESCAPED_UNICODE));
            $sw->close($fd);
        }
    }

    public function broadcast($room_id, $my_fd, $msg)
    {
        /**
         * @var \swoole_websocket_server $sw
         */
        $sw = App::$server->sw;
        foreach ($this->getRoom($room_id) as $uid => $fd) {
            if ($my_fd == $fd)
                continue;

            $sw->push($fd, json_encode($msg, \JSON_UNESCAPED_UNICODE));
        }
    }
}