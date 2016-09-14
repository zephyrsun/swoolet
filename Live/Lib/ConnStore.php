<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/26
 * Time: 下午2:40
 */

namespace Live\Lib;

use Live\Redis\RedisSub;

class ConnStore
{
    /**
     * @var Conn $conn
     */
    public $conn;

    /**
     * @var \swoole_server $sw
     */
    public $sw;

    public $uid2fd_store;
    public $room_store;
    public $user_store;

    public function __construct($conn, $sw)
    {
        $this->conn = $conn;
        $this->sw = $sw;
        $this->sub_user = new RedisSub('sub_user');

        $this->room_store = new ConnRoomStore();
        $this->user_store = self::userStore();

        //$this->subRoom();
    }

    public function subUser($uid)
    {
        $this->sub_user->subscribe(Conn::USER_CHAT . $uid, [$this, 'msgHandler']);
    }

    public function unSubUser($uid)
    {
        $this->sub_user->unsubscribe(Conn::USER_CHAT . $uid, function ($data) {
        });
    }

    public function join($fd, $uid)
    {
        $this->uid2fd_store[$uid] = $fd;

        $this->subUser($uid);
    }

    public function leave($fd, $pause = 0)
    {
        if ($conn = $this->user_store->get($fd)) {
            $uid = $conn['uid'];

            $uid_fd = $this->getFd($uid);

            //var_dump('leave', $conn, $fd, $uid_fd, $uid);

            if ($uid_fd == $fd) {
                $this->user_store->del($fd);
                $this->unSubUser($uid);

            } elseif ($pause) {
                $this->unSubUser($uid);
            }
        }
    }

    public function joinRoom($fd, $room_id, $uid)
    {
        $this->leaveRoom($fd, $room_id, $uid);

        $this->room_store->join($room_id, $uid, $fd);

        //  var_dump('room', $this->room_store->get($room_id));
    }

    public function leaveRoom($fd, $room_id = -1, $uid = -1)
    {
        if (!$conn = $this->user_store->get($fd))
            return;

        if ($room_id == $conn['room_id'] || $uid == $conn['room_id']) {
            return;
        }

        $room_id = $conn['room_id'];
        $uid = $conn['uid'];

        // var_dump('leaveRoom', $fd, $conn, $room_id);
        if ($room_id == $uid) {
            $this->stopRoom($room_id, $uid);
        }

        $this->room_store->leave($room_id, $uid);
    }

    public function stopRoom($room_id, $uid)
    {
        $this->sendToRoom($room_id, $uid, [
            't' => Conn::TYPE_LIVE_STOP,
            'msg' => '直播结束',
        ], 'stop');
    }

    public function sendToRoom($room_id, $uid, $msg, $ext = '')
    {
        $this->conn->sendToRoom($room_id, $uid, $msg, $ext);
    }

    public function subRoom()
    {
        $sub_room = new RedisSub('sub_room');
        $sub_room->subscribe(Conn::ROOM_CHAT, [$this, 'msgHandler']);

        return $this;
    }

    public function msgHandler($data)
    {
        $data = \msgpack_unpack($data[2]);
        if (!is_array($data))
            return;

        /**
         * @var \swoole_websocket_server $sw
         */
        $sw = $this->sw;

        $a = &$data['a'];
        switch ($a) {
            case 'toRoom':

                foreach ($this->room_store->get($data['room_id']) as $uid => $fd) {
                    if ($data['uid'] == $uid) {
                        continue;
                    } elseif (!$sw->push($fd, $data['msg'])) {
                        $this->room_store->leave($data['room_id'], $uid);
                    }
                }

                if ($data['ext'] == 'stop') {
                    $uid = $data['uid'];

//                if (!\Live\isProduction()) {
//                    $this->room_msg->saveSQL(0);
//                }

                    $this->room_store->destroy($uid);
                }

                break;

            case 'toUser':
                if ($fd = $this->getFd($data['uid']))
                    $sw->push($fd, $data['msg']);

                break;

            case 'updateUser':
                if ($fd = $this->getFd($data['uid']))
                    $this->user_store->set($fd, $data['user']);

                break;
        }
    }

    public function getFd($uid)
    {
        return isset($this->uid2fd_store[$uid]) ? $this->uid2fd_store[$uid] : 0;
    }

    static public function userStore()
    {
        static $table;

        if (!$table) {
            $table = new \swoole_table(2 ^ 32);

            $table->column('fd', \swoole_table::TYPE_INT, 4);
            $table->column('room_id', \swoole_table::TYPE_INT, 8);
            $table->column('uid', \swoole_table::TYPE_INT, 8);
            $table->column('nickname', \swoole_table::TYPE_STRING, 48);
            $table->column('avatar', \swoole_table::TYPE_STRING, 70);
            $table->column('lv', \swoole_table::TYPE_INT, 4);
            $table->column('is_vip', \swoole_table::TYPE_INT, 1);
            $table->column('is_tycoon', \swoole_table::TYPE_INT, 1);
            $table->column('silence', \swoole_table::TYPE_INT, 1);

            $table->create();
        }

        return $table;
    }
}