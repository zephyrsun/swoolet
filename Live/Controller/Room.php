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
        $this->conn = \Server::$conn;
        $this->conn->subscribe();
    }

    public function join($request)
    {
        $data = parent::getValidator()->required('token')->ge('room_id', 1)->required('first', false)->getResult();
        if (!$data)
            return $data;

        $room_id = $data['room_id'];
        $token_uid = $data['token_uid'];

        $db_user = new User();
        $user = $db_user->getShowInfo($token_uid, 'simple');
        if (!$user)
            return Response::msg('登录失败', 1032);

        $rank = new Rank();
        if (!(new \Live\Redis\RoomAdmin())->isSilence($token_uid, $room_id)) {
            $this->conn->roomMsg($room_id, $token_uid, [
                't' => Conn::TYPE_ENTER,
                'user' => $user,
            ]);

            $this->conn->joinRoom($request->fd, $token_uid, $room_id, $user['nickname'], $user['avatar']);

            $rank->joinRoom($room_id, $token_uid);
        }

        if ($token_uid != $room_id)
            $user = $db_user->getShowInfo($room_id, 'lv');

        $first = $data['first'];
        if ($first)
            return Response::data([
                'm' => $_POST['m'],
                'live' => (new Live())->getLive($room_id, 'app'),
                'msg' => '欢迎光临直播间。主播身高：170cm，星座：白羊座，城市：上海市。',
                'user' => $user,
                'rank' => $rank->getRankInRoom($room_id, 0),
                'admin' => (new RoomAdmin())->isAdmin($room_id, $token_uid),
                'follow' => (new Fan())->isFollow($token_uid, $room_id),
                'num' => $rank->getRoomUserNum($room_id),
                'money' => (new Income())->getIncome($room_id),
            ]);

        return Response::msg('');
        //$this->room[$data['room_id']][$this->request->fd] = $data['uid'];
    }

    public function leave($request)
    {
        $this->conn->quitConn($request->fd);

        //todo:退出逻辑

        return Response::msg('ok');
    }

    public function sendMsg($request)
    {
        $data = parent::getValidator()->required('msg')->required('horn', false)->getResult();
        if (!$data)
            return $data;

        $conn = $this->conn->getConn($request->fd);

        if ($conn) {
            list($uid, $room_id, $nickname) = $conn;

            if ($data['horn']) {

                $to_uid = $room_id;
                $ret = (new Gift())->sendHorn($uid, $to_uid);
                if (!$ret)
                    return $ret;

                $t = Conn::TYPE_HORN;
            } else {
                $t = Conn::TYPE_MESSAGE;
            }

            $this->conn->roomMsg($room_id, $uid, [
                't' => $t,
                'user' => [
                    'uid' => $uid,
                    'nickname' => $nickname,
                    'lv' => (new UserLevel())->getLv($uid),
                ],
                'msg' => $data['msg'],
            ]);
        }

        return Response::msg('ok');
    }

    public function praise($request)
    {
        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($uid, $room_id) = $conn;

            //todo:点赞逻辑

            $user = (new User())->getUser($uid);
            $this->conn->roomMsg($room_id, $uid, [
                't' => Conn::TYPE_PRAISE,
                'n' => 1,
                'user' => [
                    'uid' => $user['uid'],
                    'nickname' => $user['nickname'],
                ],
            ]);
        }

        return Response::msg('ok');
    }

    public function follow($request)
    {
        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($uid, $room_id) = $conn;

            $follow_uid = $room_id;
            $ret = (new Fan())->beFan($uid, $follow_uid);
            if ($ret) {
                $this->conn->roomMsg($room_id, $uid, [
                    't' => Conn::TYPE_FOLLOW,
                    'user' => [
                        'uid' => $uid,
                        'nickname' => "nickname{$uid}",
                    ],
                    'msg' => '关注了主播',
                ]);
            }
        }

        return Response::msg('ok');
    }

    public function sendGift($request)
    {
        $data = parent::getValidator()->required('gift_id')->getResult();
        if (!$data)
            return $data;

        $conn = $this->conn->getConn($request->fd);
        if ($conn) {
            list($uid, $room_id) = $conn;

            $gift_id = $data['gift_id'];
            $to_uid = $room_id;

            if ($uid == $to_uid)
                return Response::msg('礼物不能送给自己', 1023);

            if (!$ret = (new Gift())->sendGift($uid, $to_uid, $gift_id))
                return $ret;

            $this->conn->roomMsg($room_id, $uid, [
                't' => Conn::TYPE_GIFT,
                'user' => [
                    'uid' => $uid,
                    'nickname' => "nickname{$uid}",
                ],
                'msg' => '送给主播',
                'gift_id' => $gift_id,
            ]);
        }

        return Response::msg('ok');
    }

    public function start($request)
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];

        $this->conn->createRoom($request->fd, $token_uid);

        if(!$data = (new \Live\Lib\Live())->start($token_uid))
            return $data;

        return Response::data([
            'm' => $_POST['m'],
            'publish_url' => $data['publish_url'],
        ]);
    }

    public function stop($request)
    {
        $conn = $this->conn->getConn($request->fd);

        if ($conn) {
            list($uid, $room_id) = $conn;

            $this->conn->destroyRoom($room_id, $request->fd);

            $data = (new \Live\Lib\Live())->stop($uid);

            return Response::data([
                't' => Conn::TYPE_LIVE_STOP,
                'd' => $data,
            ]);
        }

        return Response::msg('ok');
    }
}