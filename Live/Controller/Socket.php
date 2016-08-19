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

        //$ret = \Swoolet\App::$server->sw->bind($request->fd, '11423_432141324');
        //var_dump('aaaa', $request->fd, $ret, \Swoolet\App::$server->sw->connection_info($request->fd));

        $this->conn->join($request->fd, $token_uid);

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
            list($fd_uid) = $conn;

            if ($fd_uid == $data['uid'])
                return Response::msg('自己和自己聊天是一种什么感受?');

            $this->conn->sendToUser($data['uid'], [
                't' => Conn::TYPE_CHAT,
                'msg' => [
                    'uid' => $fd_uid,
                    'msg' => $data['msg'],
                ],
            ]);
        }

        return Response::msg('ok');

    }
}