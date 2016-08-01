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
        \Server::$conn = $this->conn = new Conn();
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
        $data = parent::getValidator()->required('msg')->required('horn', false)->getResult();
        if (!$data)
            return;

        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($uid, $room_id) = $conn;

            if ($data['horn']) {

                //todo:扣钱逻辑

                $t = Conn::TYPE_HORN;
            } else {
                $t = Conn::TYPE_MESSAGE;
            }

            $this->conn->broadcast($room_id, [
                't' => $t,
                'uid' => $uid,
                'nickname' => "nickname{$uid}",
                'msg' => $data['msg'],
            ]);

            Response::msg('发送成功');
        }
    }

    public function quit($request)
    {
        $data = parent::getValidator()->ge('room_id', 1)->getResult();

        $room_id = $data['room_id'];

        $this->conn->quitRoom($request->fd, $room_id);
    }

    public function praise($request)
    {
        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($uid, $room_id) = $conn;

            $this->conn->broadcast($room_id, [
                't' => Conn::TYPE_PRAISE,
                'n' => 1,
            ]);
        }
    }

    public function follow($request)
    {
        $data = parent::getValidator()->required('to_uid')->getResult();
        if (!$data)
            return;

        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($uid, $room_id) = $conn;

            //todo:关注逻辑

            $this->conn->broadcast($room_id, [
                't' => Conn::TYPE_FOLLOW,
                'uid' => $uid,
                'nickname' => "nickname{$uid}",
                'msg' => '关注了主播',
            ]);
        }
    }

    public function sendGift($request)
    {
        $data = parent::getValidator()->required('to_uid', false)->getResult();
        if (!$data)
            return;

        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($uid, $room_id) = $conn;

            //todo:送礼逻辑

            $this->conn->broadcast($room_id, [
                't' => Conn::TYPE_GIFT,
                'uid' => $uid,
                'nickname' => "nickname{$uid}",
                'msg' => '送给主播',
            ]);
        }

    }
}