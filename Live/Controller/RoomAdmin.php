<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/28
 * Time: 下午4:04
 */

namespace Live\Controller;

use Live\Database\User;
use \Live\Response;
use \Live\Lib\Conn;

class RoomAdmin extends Basic
{
    public $conn;

    public function __construct()
    {
        $this->conn = \Server::$conn;
    }

    public function add($request)
    {
        $data = parent::getValidator()->required('token')->ge('uid', 1)->getResult();
        if (!$data)
            return $data;

        $admin_uid = $data['uid'];
        $room_id = $token_uid = $data['token_uid'];

        $ret = (new \Live\Database\RoomAdmin())->add($token_uid, $admin_uid);

        if ($ret) {
            $this->conn->broadcast($room_id, $request->fd, [
                't' => Conn::TYPE_SYS_MESSAGE,
                'user' => (new User())->getShowInfo($admin_uid, 'simple'),
                'msg' => ':nickname被任命为管理员',
            ]);
        }

        return Response::msg('ok');
    }

    public function del($request)
    {
        $data = parent::getValidator()->required('token')->ge('uid', 1)->getResult();
        if (!$data)
            return $data;

        $admin_uid = $data['uid'];
        $token_uid = $data['token_uid'];

        $ret = (new RoomAdmin())->del($token_uid, $admin_uid);

        return Response::msg('ok');
    }

    /**
     * 禁言是2小时
     * @param $request
     * @return null
     */
    public function silenceUser($request)
    {
        $data = parent::getValidator()->ge('uid', 1)->getResult();
        if (!$data)
            return $data;

        $conn = $this->conn->getConn($request->fd);

        if ($conn) {
            list($uid, $room_id) = $conn;

            $to_uid = $data['uid'];

            if ($uid != $room_id || !(new \Live\Database\RoomAdmin())->isAdmin($room_id, $to_uid))
                return Response::msg('没有权限', 1031);

            $this->conn->quitConn($request->fd);

            (new \Live\Redis\RoomAdmin())->silenceUser($to_uid, $room_id);
        }

        return Response::msg('ok');
    }
}