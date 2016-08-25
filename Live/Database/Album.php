<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;

class Album extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'user_';

    public function __construct()
    {
        $this->option['dbname'] = 'live_album';

        parent::__construct();
    }

    public function add($uid, $photo, $title)
    {
        $this->table($uid);

        return parent::insert([
            'uid' => $uid,
            'title' => $title,
            'photo' => $photo,
            'status' => 1,
            'create_ts' => \Swoolet\App::$ts,
        ]);
    }

    public function getList($uid, $start, $limit = 8)
    {
        $this->table($uid)->limit($limit);

        $this->select('id AS `key`,title,photo')->where('uid = ? AND id > ?', [$uid, $start]);

        return $this->fetchAll();
    }
}