<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Live\Redis\Rank;
use Live\Response;
use Swoolet\Data\PDO;

class Banner extends Basic
{
    public $cfg_key = 'db_1';

    public $key_banner = 'banner:all';

    public function __construct()
    {
        $this->option['dbname'] = 'live';

        PDO::__construct();

        $this->cache = new \Live\Redis\Banner();
    }

    public function table($key)
    {
        return PDO::table('banner');
    }

    public function add($title, $img, $content, $sort)
    {
        $ret = $this->table('')->insert([
            'title' => $title,
            'img' => $img,
            'content' => $content,
            'sort' => $sort,
            'status' => 1,
            'ts' => \Swoolet\App::$ts,
        ]);

        if ($ret)
            $this->cache->del($this->key_banner);

        return $ret;
    }

    public function getAll($force = false)
    {
        if ($force || !$ret = $this->cache->get($this->key_banner)) {
            $data = $this->table(1)->where('status', 1)->orderBy('sort ASC')->fetchAll();

            $ret = [];
            foreach ($data as $row) {
                unset($row['sort'], $row['status']);
                $ret[$row['id']] = $row;
            }

            $this->cache->set($this->key_banner, $ret);
        }

        return $ret;
    }

    public function getBanner($id)
    {
        $all = $this->getAll();
        $item = &$all[$id];
        if ($item) {
            return $item;
        }

        return [];
    }
}