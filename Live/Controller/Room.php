<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/28
 * Time: 下午4:04
 */

namespace Live\Controller;

use Live\Database\Gift;
use \Live\Response;
use \Live\Lib\Conn;
use Swoolet\App;

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

    public function enter()
    {
        $data = parent::getValidator()->required('token')->ge('room_id', 1)->getResult();
        if (!$data)
            return;

        $room_id = $data['room_id'];
        $uid = $data['uid'];

        $this->conn->broadcast($room_id, [
            't' => Conn::TYPE_ENTER,
            'uid' => $uid,
            'nickname' => "nickname{$uid}",
        ]);

        $this->conn->enterRoom(App::$server->frame->fd, $uid, $room_id);

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
        $this->conn->quitConn($request->fd);

        //todo:退出逻辑
    }

    public function praise($request)
    {
        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($uid, $room_id) = $conn;

            //todo:点赞逻辑

            $this->conn->broadcast($room_id, [
                't' => Conn::TYPE_PRAISE,
                'n' => 1,
            ]);
        }
    }

    public function follow($request)
    {
        $data = parent::getValidator()->getResult();
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
        $data = parent::getValidator()->required('gift_id')->getResult();
        if (!$data)
            return;

        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($uid, $room_id) = $conn;

            $gift_id = $data['gift_id'];
            $to_uid = $room_id;

            $ret = (new Gift())->sendGift($uid, $to_uid, $gift_id);
            if (!$ret)
                return $ret;

            $this->conn->broadcast($room_id, [
                't' => Conn::TYPE_GIFT,
                'uid' => $uid,
                'nickname' => "nickname{$uid}",
                'msg' => '送给主播',
                'gift_id' => $gift_id,
            ]);
        }
    }

    public function createRoom($request)
    {

    }
}