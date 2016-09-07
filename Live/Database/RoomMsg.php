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

    public function __construct()
    {
        $this->option['dbname'] = 'live_room_msg';

        parent::__construct();

        //$this->cache = new \Live\Redis\User();
    }

    public function addFromChat($room_id, $data)
    {
        $this->hashTable($room_id);
        $table = $this->clause['table'];

        $sql = '';
        $n = 0;
        foreach ($data as $row) {
            $v = $room_id . ", '" . implode("', '", $row) . "'";

            $sql .= "INSERT INTO `$table` (`room_id`,`from_uid`,`msg`,`ts`) VALUE ($v);";

            if ($n++ == 20) {
                $this->query($sql);
                $sql = '';
                $n = 0;
            }
        }

        if ($sql) {
            $this->query($sql);
        }
    }

    public function getByTS($room_id, $start_ts, $end_ts)
    {
        $this->hashTable($room_id)->select('id,from_uid,msg,ts')
            ->where('room_id = ? AND ts > ? AND ts < ?', [$room_id, $start_ts, $end_ts]);

        return $this->fetchAll();
    }
}