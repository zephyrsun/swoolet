<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Swoolet\Data\PDO;

class Basic extends PDO
{
    /**
     * @var \Live\Redis\Common $cache
     */
    public $cache;

    public $table_prefix = '';
    public $table_mod = 1e6;

    public $limit = 20;
    public $timeout = 259200;

    public function hashTable($key)
    {
        $mod = (int)($key / $this->table_mod);

        parent::table($this->table_prefix . $mod);

        return $this;
    }

    public function getAllTable()
    {
        $ret = $this->query("SHOW TABLES LIKE '{$this->table_prefix}%'")->fetchAll();

        $table = [];
        foreach ($ret as $row)
            $table[] = current($row);

        return $table;
    }

    public function getWithCache($key, $callback, $timeout = 259200)
    {
        if (true || !$data = $this->cache->get($key)) {
            if ($data = $callback()) {
                $this->cache->set($key, $data, $timeout);
            }
        }

        return $data;
    }


    public function getListWithCount($key, $key_count, $start_id, $limit, $cb, $unpack = true)
    {
        $count = 0;

        if ($list = $this->cache->revRange($key, $start_id, $limit, true)) {
            if ($key_count)
                $count = $this->cache->getCount($key_count);

        } elseif ($data = $cb()) {

            array_unshift($data, $key);

            //缓存count
            if ($key_count) {
                $count = $this->fetchCount();
                // $this->cache->incrCount($key_count, $count, $this->timeout);
                $this->cache->link->set($key_count, $count, $this->timeout);
            }

            //缓存列表
            call_user_func_array([$this->cache->link, 'zAdd'], $data);
            $this->cache->expire($key, $this->timeout);

            $list = $this->cache->revRange($key, $start_id, $limit, true);
        }

        if ($unpack) {
            $new_list = [];
            foreach ($list as $val => $key) {
                $val = \msgpack_unpack($val);
                $val['key'] = $key;
                $new_list[] = $val;
            }

            $list = $new_list;
        }

        return [$list, $count];
    }

    public function del($uid, $id)
    {
        $this->hashTable($uid);

        $this->where('uid', $uid)->where('id', 'IN', explode(',', $id));

        return $this->delete();
    }

}