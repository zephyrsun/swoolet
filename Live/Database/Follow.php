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

    public $limit = 500;

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
            $this->cache->del($this->key . $uid);

        return $ret;
    }

    public function del($uid, $ref_uid)
    {
        $ret = $this->table($uid)->where('uid = ? AND ref_uid = ?', [$uid, $ref_uid])->delete();

        $ret = $this->cache->link->zRem($this->key . $uid, $ref_uid);

        return $ret;
    }

    public function getCount($uid)
    {
        $arr = $this->getList($uid, 0, 1);
        return $arr[1];
    }

    public function getList($uid, $start_id, $limit = 0)
    {
        $key = $this->key . $uid;
        $key_count = $this->key_count . $uid;

        $limit or $limit = $this->limit;

        if ($list = $this->cache->revRange($key, $start_id, $limit, true)) {
            $count = $this->cache->getCount($key_count);
        } else {
            $count = 0;

            $list = $this->table($uid)->where('uid = ? AND id > ?', [$uid, $start_id])->limit($this->limit)->fetchAll();
            if ($list) {
                $n = 0;
                $data = [$key];
                foreach ($list as $row) {
                    $data[] = $n++;
                    $data[] = $row['ref_uid'];
                }

                //缓存人数
                $count = $this->fetchCount();
                $this->cache->incrCount($key_count, $count, $this->timeout);

                //缓存列表
                call_user_func_array([$this->cache->link, 'zAdd'], $data);
                $this->cache->expire($key, $this->timeout);

                $list = $this->cache->revRange($key, $start_id, $this->limit, true);
            }
        }

        return [$list, $count];
    }
}