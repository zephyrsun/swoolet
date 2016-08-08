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
        $this->conn = Conn::getInstance();
    }

    public function add($request)
    {
        $data = parent::getValidator()->required('token')->ge('admin_uid', 1)->getResult();
        if (!$data)
            return $data;

        $admin_uid = $data['admin_uid'];
        $room_id = $token_uid = $data['token_uid'];

        $ret = (new \Live\Database\RoomAdmin())->add($token_uid, $admin_uid);

        if ($ret) {
            $this->conn->broadcast($room_id, [
                't' => Conn::TYPE_SYS_MESSAGE,
                'user' => (new User())->getShowInfo($admin_uid, 'simple'),
                'msg' => ':nickname被任命为管理员',
            ]);
        }

        return Response::msg('ok');
    }

    public function del($request)
    {
        $data = parent::getValidator()->required('token')->ge('admin_uid', 1)->getResult();
        if (!$data)
            return $data;

        $admin_uid = $data['admin_uid'];
        $token_uid = $data['token_uid'];

        $ret = (new RoomAdmin())->del($token_uid, $admin_uid);

        return Response::msg('ok');
    }
}