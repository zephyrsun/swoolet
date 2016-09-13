<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;

use Swoolet\App;
use Swoolet\Data\PDO;

class RoomMsg extends Basic
{
    public $cfg_key = 'db_1';

    public $table_prefix = 'room_';

    public $sql = [];

    public function __construct()
    {
        $this->option['dbname'] = 'live_room_msg';

        parent::__construct();

        //$this->cache = new \Live\Redis\User();
    }

    public function save($room_id, $uid, $msg, $ts)
    {
        $this->hashTable($room_id);

        $this->sql[] = "INSERT INTO `{$this->clause['table']}` (`room_id`,`from_uid`,`msg`,`ts`) VALUE ($room_id, $uid, '$msg', '$ts');";

        $this->saveSQL(20);
    }

    public function saveSQL($n)
    {
        if (count($this->sql) < $n)
            return;

        $sql = $this->sql;
        $this->sql = [];

        $this->query(implode('', $sql));
    }

//    public function addFromChat($room_id, $data)
//    {
//        $this->hashTable($room_id);
//        $table = $this->clause['table'];
//
//        $sql = '';
//        $n = 0;
//        foreach ($data as $row) {
//            $v = $room_id . ", '" . implode("', '", $row) . "'";
//
//            $sql .= "INSERT INTO `$table` (`room_id`,`from_uid`,`msg`,`ts`) VALUE ($v);";
//
//            if ($n++ == 20) {
//                $this->query($sql);
//                $sql = '';
//                $n = 0;
//            }
//        }
//
//        if ($sql) {
//            $this->query($sql);
//        }
//    }

    public function getByTS($room_id, $start_ts, $end_ts, $replay_start_ts = 0)
    {
        $this->hashTable($room_id)->select("id,from_uid,msg,ts - $replay_start_ts as ts")
            ->where('room_id = ? AND ts > ? AND ts < ?', [$room_id, $start_ts, $end_ts]);

        return $this->fetchAll();
    }
}