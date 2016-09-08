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

    public $key_banner = 'banner:';

    const TYPE_BANNER = 1;
    const TYPE_SPLASH = 2;

    public function __construct()
    {
        $this->option['dbname'] = 'live';

        PDO::__construct();

        $this->cache = new \Live\Redis\Banner();
    }

    public function hashTable($key)
    {
        return parent::table('banner');
    }

    public function add($title, $img, $content, $sort)
    {
        $ret = $this->hashTable('')->insert([
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

    /**
     * @param bool $force
     * @return array
     */
    public function getAllBanner($force = false)
    {
        $type = self::TYPE_BANNER;
        $key = $this->key_banner . $type;
        if ($force || !$ret = $this->cache->get($key)) {
            $data = $this->hashTable($type)->where('status = ? AND type = ?', [1, $type])
                ->orderBy('sort DESC')->fetchAll();

            $ret = [];
            foreach ($data as $row) {
                unset($row['sort'], $row['status']);
                $ret[$row['id']] = $row;
            }

            $this->cache->set($key, $ret);
        }

        return $ret;
    }

    public function getBanner($id)
    {
        $all = $this->getAllBanner();
        $item = &$all[$id];
        if ($item) {
            return $item;
        }

        return [];
    }

    public function getSplash($force = false)
    {
        $type = self::TYPE_SPLASH;
        $key = $this->key_banner . $type;
        if ($force || !$ret = $this->cache->get($key)) {
            $ret = $this->hashTable($type)->select('img')->where('status = ? AND type = ?', [1, $type])
                ->orderBy('id DESC')->fetch();

            if ($ret) {
                $this->cache->set($key, $ret);
            }
        }

        return $ret;
    }
}