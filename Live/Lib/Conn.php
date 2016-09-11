<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/26
 * Time: 下午2:40
 */

namespace Live\Lib;

use Live\Database\RoomMsg;
use Live\Redis\RedisPub;
use Live\Redis\RedisSub;

class Conn
{
    const TYPE_MESSAGE = 1;//普通消息
    const TYPE_HORN = 2;//广播喇叭
    const TYPE_FOLLOW = 3;//关注主播
    const TYPE_ENTER = 4;//进入房间
    const TYPE_PRAISE = 5;//点赞
    const TYPE_ROOM_BROADCAST = 6;//房间广播,系统消息
    const TYPE_ROOM_ONE = 7;//房间,只自己收到
    const TYPE_CHAT = 8;//私信

    const TYPE_GIFT = 10;//送礼
    const TYPE_LIVE_STOP = 20;//停播

    const TYPE_OFFLINE_CHAT_MSG = 'offlineMsg';

    public $msg = [];

    public $sw;

    public $sub_user;
    public $pub;

    public $store_user;
    public $store_room;
    public $room_msg;

    const ROOM_CHAT = 'room_chat:';
    const USER_CHAT = 'user_chat:';

    static public $subscribe = false;

    public function __construct()
    {
        $this->sw = \Swoolet\App::$server->sw;

        $this->sub_user = new RedisSub('sub_user');
        $this->pub = new RedisPub();

        $this->store_user = ConnUserStorage::getInstance();
        $this->store_room = ConnRoomStorage::getInstance();

        $this->room_msg = new RoomMsg();
    }

    static public function getInstance()
    {
        static $ins;

        $ins or $ins = new Conn();

        return $ins;
    }

    public function getConn($fd)
    {
        if ($user = $this->store_user->get($fd)) {
            $room_id = $user['room_id'];

            unset($user['fd'], $user['room_id']);

            return [$room_id, $user];
        }

        return $user;
    }


    public function createRoom($fd, $uid, $user)
    {
        $this->store_room->create($uid);

        $this->joinRoom($uid, $fd, $uid, $user);
    }

    public function stopRoom($room_id, $uid)
    {
        $this->sendToRoom($room_id, $uid, [
            't' => Conn::TYPE_LIVE_STOP,
            'msg' => '直播结束',
        ], 'stop');
    }

    public function join($fd, $uid, $user)
    {
        $this->subUser($uid, $fd);

        //$this->sw->bind($fd, $uid);

        $this->joinRoom(0, $fd, $uid, $user);
    }

    public function leave($fd, $unset = true)
    {
        $conn = $this->store_user->get($fd);
        if ($conn && $fd == $conn['fd']) {
            if ($unset)
                $this->store_user->del($fd);

            $this->sub_user->unsubscribe(self::USER_CHAT . $conn['uid'], function ($data) {
            });
        }
    }

    public function joinRoom($room_id, $fd, $uid, $user)
    {
        //退出已经存在的房间
        if ($room_id) {
            $this->leaveRoom($fd);

            $this->store_room->join($room_id, $fd, $uid);
        }

        $ret = $this->store_user->set($fd, [
            'fd' => $fd,
            'room_id' => $room_id,
            'uid' => $user['uid'],
            'nickname' => $user['nickname'],
            'lv' => $user['lv'],
            'is_vip' => $user['is_vip'],
            'is_tycoon' => $user['is_tycoon'],
        ]);

        //\Swoolet\Log(json_encode($this->room), 'joinRom');
    }

    public function leaveRoom($fd)
    {
        $conn = $this->store_user->get($fd);
        if ($conn) {
            $room_id = $conn['room_id'];
            $uid = $conn['uid'];

            if ($this->store_room->getRoom($room_id)) {
                $this->stopRoom($room_id, $uid);
            } else {
                $this->store_user->del($fd);
                $this->store_room->leave($room_id, $fd);
            }
        }

        return $conn;
    }

    public function subUser($uid, $fd)
    {
        //$sub = new RedisAsync('redis_async', $uid);
        //var_dump('subUser', $uid, $fd);
        $this->sub_user->subscribe(self::USER_CHAT . $uid, function ($data) use ($fd) {
            $this->msgAction($data, $fd);
        });
    }

    /**
     * @param \swoole_server $sw
     */
    public function subRoom($sw)
    {
        $process = new \swoole_process(function ($process) {

            $sub = new RedisSub('sub_room');
            $sub->subscribe(self::ROOM_CHAT, function ($data) {
                //  [$this, 'msgAction'];
                $this->msgAction($data);
            });
        });

        $sw->addProcess($process);

        //$this->store_room->bindProcess($process);

        $sw->addProcess($this->store_room->process());
    }

    public function msgAction($data, $sub_fd = 0)
    {
//        if (!is_array($data))
//            return;

        $data = \msgpack_unpack($data[2]);
        if (!is_array($data))
            return;

        /**
         * @var \swoole_websocket_server $sw
         */
        $sw = $this->sw;

        $a = &$data['a'];
        if ($a == 'toRoom') {
            //$send_uid != $uid

            //\Swoolet\Log(json_encode($this->getRoom($data['room_id'])), 'Room');
            foreach ($this->store_room->getRoom($data['room_id']) as $fd => $uid) {
                if ($data['uid'] == $uid) {
                    continue;
                } elseif (!$sw->push($fd, $data['msg'])) {
                    $this->store_room->leave($data['room_id'], $uid);
                }
            }

            if ($data['ext'] == 'stop') {
                $uid = $data['uid'];

//                if (!\Live\isProduction()) {
//                    $this->room_msg->saveSQL(0);
//                }

                $this->store_room->destroy($uid);
            }

        } elseif ($a == 'toUser') {
            $sw->push($sub_fd, $data['msg']);

        } elseif ($a == 'updateUser') {
            $this->store_user->set($sub_fd, $data['user']);
        }
    }

    public function sendToRoom($room_id, $uid, $msg, $ext = '')
    {
        $json_msg = json_encode($msg, \JSON_UNESCAPED_UNICODE);

        $msg = [
            'a' => 'toRoom',
            'ext' => $ext,
            'room_id' => $room_id,
            'uid' => $uid,
            'msg' => $json_msg,
        ];

        $this->pub->publish(self::ROOM_CHAT, \msgpack_pack($msg));

        // save msg
        $this->room_msg->save($room_id, $uid, $json_msg, \Swoolet\App::$ts);
    }

    public function updateUser($uid, $user)
    {
        $msg = [
            'a' => 'updateUser',
            'uid' => $uid,
            'user' => $user,
        ];

        $this->pub->publish(self::USER_CHAT . $uid, \msgpack_pack($msg));
    }

    public function sendToUser($uid, $msg, callable $cb = null)
    {
        $data = [
            'a' => 'toUser',
            'uid' => $uid,
            'msg' => json_encode($msg, \JSON_UNESCAPED_UNICODE),
        ];

        $ret = $this->pub->publish(self::USER_CHAT . $uid, \msgpack_pack($data));
        $cb && $cb($ret);
    }

    public function onWorkerStart($sw, $worker_id)
    {


//        $filename = "/tmp/swoolet_ws_{$worker_id}.php";
//        $arr = File::get($filename, true);
//        if ($arr) {
//            // \Swoolet\Log(json_encode($arr), 'recover');
//
//            list($this->uid, $this->conn, $this->room) = $arr;
//
//            //var_dump('start', $this->room);
//
//            //重新监听个人聊天
//            if ($this->uid) {
//                foreach ($this->uid as $uid => $fd) {
//                    $this->subUser($uid, $fd);
//                }
//            }
//
//            File::rm($filename);
//        }
//
//        $this->sw->connection_list(0, 100);

        return $this;
    }

    public function onWorkerStop($sw, $worker_id)
    {
        //RedisSub::release('sub_room');
        //RedisSub::release('sub_user');
    }
}