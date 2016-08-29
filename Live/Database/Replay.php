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

    public $key_replay = 'replay:';
    public $key_replay_count = 'replay_n:';

    public function __construct()
    {
        $this->option['dbname'] = 'live_replay';

        parent::__construct();

        $this->cache = new \Live\Redis\Album();
    }

    public function saveReplay($uid, $data)
    {
        $data += [
            'uid' => $uid,
            'status' => 1,
            'create_ts' => \Swoolet\App::$ts,
        ];

        $ret = $this->table($uid)->insert($data);
        if ($ret) {
            $this->cache->link->del($this->key_replay . $uid, $this->key_replay_count . $uid);
        }

        return $ret;
    }

    public function getList($uid, $start, $limit = 8)
    {
        $ret = parent::getListWithCount($this->key_replay . $uid, $this->key_replay_count . $uid, $start, $limit, function () use ($uid, $start, $limit) {
            $this->table($uid)->limit(500);

            $this->select('id AS `key`,title,cover,play_url')->orderBy('id DESC')->where('uid = ? AND id > ?', [$uid, $start]);
            if ($list = $this->fetchAll()) {
                $data = [];
                foreach ($list as $row) {
                    $data[] = $row['key'];
                    $data[] = \msgpack_pack($row);
                }

                return $data;
            }

            return $list;
        }, true);

        return ['replay' => $ret[0], 'replay_total' => $ret[1]];
    }
}