<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/28
 * Time: 下午4:04
 */

namespace Live\Controller;

use \Live\Response;
use \Live\Lib\Conn;

class Room extends Basic
{
    /**
     * @var \Live\Lib\Conn
     */
    public $conn;

    /**
     * 不会执行第二次
     * Room constructor.
     */
    public function __construct()
    {
        \WebSocket::$conn = $this->conn = new Conn();
    }

    public function enter($request)
    {
        $data = parent::getValidator()->ge('uid', 1)->ge('room_id', 1)->getResult();
        if (!$data)
            return;

        $room_id = $data['room_id'];
        $uid = $data['uid'];

        $this->conn->broadcast($room_id, [
            't' => Conn::TYPE_ENTER,
            'uid' => $uid,
            'nickname' => "nickname{$uid}",
        ]);

        $this->conn->enterRoom($request->fd, $uid, $room_id);

        Response::msg('登陆成功');
        //$this->room[$data['room_id']][$this->request->fd] = $data['uid'];
    }

    public function sendMsg($request)
    {
        $data = parent::getValidator()->required('msg')->getResult();
        if (!$data)
            return;

        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($uid, $room_id) = $conn;

            $this->conn->broadcast($room_id, [
                't' => Conn::TYPE_MESSAGE,
                'uid' => $uid,
                'nickname' => "nickname{$uid}",
                'msg' => $data['msg'],
            ]);

            Response::msg('发送成功');
        }
    }

    public function quit($request)
    {
        $data = \Live\getParams(function ($v) {
            /**
             * @var \Swoolet\Lib\Validator $v
             */
            $v->ge('room_id', 1);
        });

        $room_id = $data['room_id'];

        $this->conn->quitRoom($request->fd, $room_id);
    }
}