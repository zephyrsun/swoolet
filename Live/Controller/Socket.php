<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/28
 * Time: 下午4:04
 */

namespace Live\Controller;

use Live\Database\Fan;
use Live\Database\Follow;
use Live\Database\Gift;
use Live\Database\Income;
use Live\Database\Live;
use Live\Database\User;
use Live\Database\RoomAdmin;
use Live\Database\UserLevel;
use Live\Redis\Rank;
use \Live\Response;
use \Live\Lib\Conn;
use Swoolet\App;

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
        $this->conn->subscribe();
    }

    public function init($request)
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];
        //$ret = App::$server->sw->bind($request->fd, $token_uid);
        //var_dump('aaaa', $request->fd, $ret, App::$server->sw->connection_info($request->fd));

        $this->conn->join($request->fd, $token_uid, 0, []);

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

        $conn = $this->conn->getInfo($request->fd);
        if ($conn) {
            list($from_uid) = $conn;
            $this->conn->sendToUser($from_uid, $data['uid'], $data['msg']);
        }

        return Response::msg('ok');

    }
}