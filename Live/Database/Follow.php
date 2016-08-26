<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午2:59
 */

namespace Live\Database;

use Swoolet\Data\PDO;

class Follow extends Basic
{
    public $cfg_key = 'db_1';

    public $table_prefix = 'follow_';
    public $key = 'follow:';
    public $key_count = 'follow_n:';

    public function __construct()
    {
        $this->option['dbname'] = 'live_follow';

        PDO::__construct();

        $this->cache = new \Live\Redis\Common();
    }

    /**
     * @param $uid
     * @param $ref_uid
     * @return bool
     */
    public function add($uid, $ref_uid)
    {
        $ret = $this->table($uid)->insert([
            'uid' => $uid,
            'ref_uid' => $ref_uid,
        ], 'INSERT IGNORE');

        if ($ret)
            $this->cache->link->del($this->key . $uid, $this->key_count . $uid);

        return $ret;
    }

    public function del($uid, $ref_uid)
    {
        $ret = $this->table($uid)->where('uid = ? AND ref_uid = ?', [$uid, $ref_uid])->delete();

        $ret = $this->cache->link->zRem($this->key . $uid, $ref_uid);
        if ($ret)
            $this->cache->link->del($this->key . $uid, $this->key_count . $uid);

        return $ret;
    }

    public function getCount($uid)
    {
        $arr = $this->getList($uid, 0, 1);
        return $arr[1];
    }

    public function getList($uid, $start, $limit)
    {
        return parent::getListWithCount($this->key . $uid, $this->key_count . $uid, $start, $limit, function () use ($uid, $start, $limit) {
            $this->table($uid)->limit(500);

            $this->table($uid)->select('id,ref_uid')->orderBy('id DESC')->where('uid', $uid);
            if ($list = $this->fetchAll()) {
                $data = [];
                foreach ($list as $row) {
                    $data[] = $row['id'];
                    $data[] = $row['ref_uid'];
                }

                return $data;
            }

            return $list;
        }, false);
    }
}