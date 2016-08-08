<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


class Replay extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'user_';

    public function __construct()
    {
        $this->option['dbname'] = 'live_replay';

        parent::__construct();

        //$this->cache = new \Live\Redis\User();
    }

    public function saveReplay($uid, $data)
    {
        $data += [
            'uid' => $uid,
            'status' => 1,
            'create_ts' => \Swoolet\App::$ts,
        ];

        return $this->table($uid)->insert($data);
    }
}