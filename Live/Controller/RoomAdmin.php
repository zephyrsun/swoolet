<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/28
 * Time: 下午4:04
 */

namespace Live\Controller;

use Live\Database\ReportUser;
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
            $this->conn->updateUser($admin_uid, ['admin' => true]);

            $this->conn->sendToRoom($room_id, $token_uid, [
                't' => Conn::TYPE_ROOM_BROADCAST,
                'user' => (new \Live\Database\User())->getShowInfo($admin_uid, 'simple'),
                'msg' => '[nickname]被任命为管理员',
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
        $room_id = $token_uid = $data['token_uid'];

        $ret = (new \Live\Database\RoomAdmin())->del($token_uid, $admin_uid);
        if ($ret) {
            $this->conn->updateUser($admin_uid, ['admin' => false]);

            $this->conn->sendToUser($admin_uid, [
                't' => Conn::TYPE_ROOM_ONE,
                'msg' => '主播取消了您的房管权限',
            ]);
        }

        return Response::msg('ok');
    }

    /**
     * 禁言是2小时
     * @param $request
     * @return null
     */
    public function silenceUser($request)
    {
        $data = parent::getValidator()->required('token')->required('room_id')->ge('uid', 1)->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];
        $room_id = $data['room_id'];
        $to_uid = $data['uid'];

        if ($token_uid != $room_id && !(new \Live\Database\RoomAdmin())->isAdmin($room_id, $token_uid))
            return Response::msg('没有权限', 1031);

        $ret = (new \Live\Database\RoomAdmin())->silenceUser($room_id, $to_uid);
        if ($ret) {
            $this->conn->updateUser($to_uid, ['silence' => true]);

            $this->conn->sendToUser($to_uid, [
                't' => Conn::TYPE_ROOM_ONE,
                'msg' => '你已被禁言两小时，走正道，说人话！',
            ]);

            $user = (new \Live\Database\User())->getShowInfo($to_uid, 'simple');
            $this->conn->sendToRoom($room_id, $to_uid, [
                't' => Conn::TYPE_ROOM_BROADCAST,
                'msg' => "[nickname]已被禁言两小时，走正道，说人话！",
                'user' => $user,
            ]);
        }

        return Response::msg('ok');
    }

    public function stopSilenceUser()
    {
        $data = parent::getValidator()->required('token')->required('room_id')->ge('uid', 1)->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];
        $room_id = $data['room_id'];
        $to_uid = $data['uid'];

        if ($token_uid != $room_id && !(new \Live\Database\RoomAdmin())->isAdmin($room_id, $token_uid))
            return Response::msg('没有权限', 1032);

        $ret = (new \Live\Database\RoomAdmin())->silenceUser($room_id, $to_uid);
        if ($ret) {
            $this->conn->updateUser($to_uid, ['silence' => false]);

            $this->conn->sendToUser($to_uid, [
                't' => Conn::TYPE_ROOM_ONE,
                'msg' => '主播、管理发善心，你可以发言啦！',
            ]);
        }

        return Response::msg('ok');
    }

    /**
     * 举报
     *
     * 广告欺诈
     * 淫秽色情
     * 骚扰谩骂
     * 反动政治
     * 其他内容
     */
    public function reportUser()
    {
        $data = parent::getValidator()->required('token')->required('reason')->ge('uid', 1)->getResult();
        if (!$data)
            return $data;

        $ret = (new ReportUser())->add($data['token_uid'], $data['uid'], $data['reason']);
        if (!$ret)
            return $ret;

        return Response::msg('感谢您的举报，我们将尽快处理');
    }
}