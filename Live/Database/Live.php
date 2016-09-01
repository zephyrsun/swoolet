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
    public $key_home = 'home';
    public $key_live_follow = 'live_follow:';

    const PIC_LARGE = '!pl';

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

    public function getLiveList($start_id, $sub_key = 'home', $sub_clause = null, $limit = 20)
    {
        $key = $this->key_live . $sub_key;
        $list = $this->cache->revRange($key, $start_id, $limit, true);
        if (!$list) {
            $this->cacheLiveList($sub_key, $sub_clause);
            $list = $this->cache->revRange($key, $start_id, $limit, true);
        }

        return $list;
    }

    public function cacheLiveList($sub_key, $sub_clause)
    {
        $key = $this->key_live . $sub_key;

        $this->table(0)->select('uid,title,city,cover,play_url')
            ->where('status', 1)->orderBy('ts DESC')->limit($this->limit);

        if ($sub_clause)
            $sub_clause();

        $list = $this->fetchAll();
        $db_user = new User();
        $n = 0;
        $data = [$key];
        foreach ($list as $row) {

            $row['cover'] .= self::PIC_LARGE;

            $user = $db_user->getUser($row['uid']);

            $row += [
                'nickname' => $user['nickname'],
                'zodiac' => $user['zodiac'],
            ];

            $data[] = ++$n;
            $data[] = \msgpack_pack($row);
        }

        //缓存列表
        call_user_func_array([$this->cache->link, 'zAdd'], $data);
        $this->cache->expire($key, 5);//3600

        return $data;
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

        return $data;
    }

    public function stop($uid)
    {
        return $this->table($uid)->where('uid = ? AND status = 1', $uid)->update(['status' => 0]);
    }

    public function getLive($uid, $type = 'all')
    {
        $live = $this->getWithCache($this->key_live . $uid, function () use ($uid) {
            return $this->select('uid,title,city,cover,play_url,hls_url')->orderBy('ts DESC')->table($uid)->where('uid', $uid)->fetch();
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

    public function getLiveOfFollow($uid, $start_id)
    {
        return $this->getLiveList($start_id, $uid, function () use ($uid) {
            $follow = (new Follow())->getList($uid, 0, 500);
            $this->where('uid', 'IN', array_keys($follow[0]));
        });
    }
}