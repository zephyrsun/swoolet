<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Live\Response;
use Swoolet\Data\PDO;

class Live extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'live';
    public $key_live = 'live:';

    public function __construct()
    {
        $this->option['dbname'] = 'live';

        parent::__construct();

        $this->cache = new \Live\Redis\Room();
    }

    public function table($key)
    {
        PDO::table($this->table_prefix);

        return $this;
    }

    public function updateLive($uid, $new_data)
    {
        $data = $this->getLive($uid);

        if ($data) {
            $ret = $this->table($uid)->where('uid', $uid)->update($new_data);
        } else {
            $ret = $this->table($uid)->insert($new_data + ['uid' => $uid]);
        }

        if (!$ret)
            return Response::msg('开播失败', 1025);

        return $ret;
    }

    public function stop($uid)
    {
        return $this->table($uid)->where('uid = ? AND status = 1', $uid)->update(['status' => 0]);
    }

    public function getLive($uid, $type = 'app')
    {
        $live = $this->getWithCache($this->key_live . $uid, function () use ($uid) {
            return $this->table($uid)->where('uid', $uid)->fetch();
        });

        if ($live) {
            if ($type == 'app') {
                //play in app
                $live = [
                    'play_url' => $live['play_url'],
                ];
            } elseif ($type == 'h5') {
                $live = [
                    'hls_url' => $live['hls_url'],
                ];
            }
        }

        return $live;
    }
}