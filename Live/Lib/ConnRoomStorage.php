<?php

namespace Live\Lib;


class ConnRoomStorage
{
    /**
     * @var \swoole_process $process
     */
    public $process;

    public $room;

    static public function getInstance()
    {
        static $ins;

        if (!$ins) {
            $ins = new ConnRoomStorage();
        }

        return $ins;
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
                    $this->room[$room_id][$data[3]] = $data[2];
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

    public function getRoom($room_id)
    {
        $this->process->write("get|$room_id");
        return \msgpack_unpack($this->process->read());
    }

    public function create($room_id)
    {
        $this->process->write("create|$room_id");
    }

    public function destroy($room_id)
    {
        //   unset(self::$ins[$room_id]);
        $this->process->write("destroy|$room_id");
    }

    public function join($room_id, $fd, $uid)
    {
        $this->process->write("join|$room_id|$fd|$uid");
    }

    public function leave($room_id, $fd)
    {
        // unset($this->room[$room_id][$fd]);
        $this->process->write("leave|$room_id|$fd");
    }
}