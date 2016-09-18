<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/28
 * Time: 下午4:04
 */

namespace Live\Controller;

use Live\Database\ChatMsg;
use Live\Database\User;
use \Live\Response;
use \Live\Lib\Conn;
use Live\Third\JPush;

class Socket extends Basic
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
        $this->conn = \Server::$conn;
    }

    public function init($request)
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];

        $user = (new User())->getShowInfo($token_uid, 'lv');

        $this->conn->join($request->fd, $token_uid, $user);

        return Response::msg('ok');
    }

    public function resume($request)
    {
        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($room_id, $user) = $conn;

            $this->conn->join($request->fd, $user['uid'], []);

            return Response::msg('ok');
        }

        return $this->init($request);
    }

    public function pause($request)
    {
        $this->conn->leave($request->fd, 1);

        return Response::msg('ok');
    }

    public function quit($request)
    {
        $this->conn->leave($request->fd);

        return Response::msg('ok');
    }

    public function chat($request)
    {
        $data = parent::getValidator()->required('msg')->required('uid')->getResult();
        if (!$data)
            return $data;

        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($room_id, $user) = $conn;

            $from_uid = $user['uid'];

            $to_uid = $data['uid'];
            $msg = $data['msg'];

            if ($from_uid == $to_uid)
                return Response::msg('自己和自己聊天是一种什么感受?');

            if (!$user['is_vip']) {
                $ret = (new \Live\Database\Balance())->subAndLog($from_uid, $to_uid, 20);
                if (!$ret)
                    return $ret;
            }

            $cb = function ($result) use ($from_uid, $to_uid, $user, $msg) {
                if ($result)
                    return;

                //发送未成功
                (new JPush())->push("{$user['nickname']}：$msg", $to_uid, [
                    't' => Conn::TYPE_OFFLINE_CHAT_MSG,
                ]);

                (new ChatMsg())->add($to_uid, $from_uid, $msg);
            };

            $this->conn->sendToUser($to_uid, [
                't' => Conn::TYPE_CHAT,
                'user' => $user,
                'msg' => $msg,
            ], $cb);
        }

        return Response::msg('ok');
    }

    public function getServer()
    {
        $list = [
            'test.camhow.com.cn:9502',
            'test.camhow.com.cn:9502',
        ];

        Response::data([
            'm' => $_POST['m'],
            'list' => $list
        ]);
    }
}