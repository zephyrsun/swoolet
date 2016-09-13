<?php

namespace Live\Lib;

class ConnRoomStore
{
    /**
     * @var \swoole_process $process
     */
    public $process;

    public $room;

//    static public function getInstance()
//    {
//        static $ins;
//
//        $ins or $ins = new RoomStore();
//
//        return $ins;
//    }

    public function get($room_id)
    {
        //$this->process->write("get|$room_id");
        //return \msgpack_unpack($this->process->read());
        return isset($this->room[$room_id]) ? $this->room[$room_id] : [];
    }

    public function create($room_id)
    {
        //$this->process->write("create|$room_id");
        $this->room[$room_id] = [];
    }

    public function destroy($room_id)
    {
        //$this->process->write("destroy|$room_id");
        unset($this->room[$room_id]);
    }

    public function join($room_id, $uid, $fd)
    {
        //if ($room_id == $uid)
        //    $this->create($room_id);

        //$this->process->write("join|$room_id|$uid|$fd");
        $this->room[$room_id][$uid] = $fd;
    }

    public function leave($room_id, $uid)
    {
        //$this->process->write("leave|$room_id|$uid");
        unset($this->room[$room_id][$uid]);
    }

    public function process()
    {
        return $this->process = new \swoole_process(function ($process) {
            while ($data = $process->read()) {
                list($cmd, $room_id) = $data = explode('|', $data);

                if ($cmd == 'get') {
                    $room = isset($this->room[$room_id]) ? $this->room[$room_id] : [];
                    $process->write(\msgpack_pack($room));

                } elseif ($cmd == 'join') {
                    $this->room[$room_id][$data[2]] = $data[3];
                } elseif ($cmd = 'leave') {
                    unset($this->room[$room_id][$data[2]]);
                } elseif ($cmd == 'create') {
                    $this->room[$room_id] = [];
                } elseif ($cmd == 'destory') {
                    unset($this->room[$room_id]);
                }
            }
        });
    }
}