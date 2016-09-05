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

    public $key_album = 'album:';
    public $key_album_count = 'album_n:';

    public $key_album_wall = 'album:wall';

    public function __construct()
    {
        $this->option['dbname'] = 'live_album';

        parent::__construct();

        $this->cache = new \Live\Redis\Album();

    }

    /**
     * @param $uid
     * @param $photo
     * @param $title
     * @param $type
     *        - 1 相册
     *        - 2 视频
     * @return int
     */
    public function add($uid, $photo, $title, $type)
    {
        $this->table($uid);

        $ret = parent::insert([
            'uid' => $uid,
            'title' => $title,
            'type' => $type,
            'photo' => $photo,
            'status' => 1,
            'create_ts' => \Swoolet\App::$ts,
        ]);

        if ($ret) {
            $this->cache->link->del($this->key_album . $uid, $this->key_album_count . $uid);
        }

        return $ret;
    }

    public function getList($uid, $start, $limit = 8)
    {
        $ret = parent::getListWithCount($this->key_album . $uid, $this->key_album_count . $uid, $start, $limit, function () use ($uid, $start, $limit) {
            $this->table($uid)->limit(500);

            $this->select('id AS `key`,title,photo,type')->orderBy('id DESC')->where('uid', $uid);
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

        return ['album' => $ret[0], 'album_total' => $ret[1]];
    }

    public function albumWall($start, $limit = 30)
    {
        //todo:用户过百万的问题,记得修改
        $ret = parent::getListWithCount($this->key_album_wall, 0, $start, $limit, function () {
            $this->table(1)->limit(500);

            $this->select('id AS `key`,title,photo,type')->orderBy('id DESC');
            if ($list = $this->fetchAll()) {
                $data = [];
                foreach ($list as $row) {
                    $data[] = $row['key'];
                    $data[] = \msgpack_pack($row);
                }

                return $data;
            }

            return $list;
        });

        return $ret[0];
    }
}