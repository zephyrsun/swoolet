<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Live\Response;

class Live extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'live_';

    public $key_live = 'live:';

    public $key_list_latest = 'live_latest';
    public $key_list_hot = 'live_hot';
    public $key_list_data = 'live_data';

    public $key_live_follow = 'live_follow:';

    const PIC_LARGE = '!pl';

    public function __construct()
    {
        $this->option['dbname'] = 'live';

        parent::__construct();

        $this->cache = new \Live\Redis\Room();
    }

    public function getList($key, $start, $limit = 20, $list_cb = '')
    {
        //$key = $this->key_live_latest;
        $list = $this->cache->revRange($key, $start, $limit, true);
        if (!$list) {
            $this->cacheLive();
            $list = $this->cache->revRange($key, $start, $limit, true);
        }

        $ret = [];
        if ($list) {

            if ($list_cb)
                $list = $list_cb($list);

            $data = $this->cache->link->hMGet($this->key_list_data, array_keys($list));
            foreach ($data as $uid => $row) {
                $row = \msgpack_unpack($row);
                $row['key'] = $list[$uid];
                $ret[] = $row;
            }
        }

        return $ret;
    }

    public function cacheLive()
    {
        $key_latest = $this->key_list_latest;
        $key_hot = $this->key_list_hot;
        $key_data = $this->key_list_data;

        $expire = \Live\isProduction() ? 3600 : 5;

        $this->hashTable(0)->select('uid,title,city,cover,play_url')
            ->where('status', 1)->orderBy('ts DESC')->limit($this->limit);

        $ds_user = new User();
        $ds_rank = new \Live\Redis\Rank();

        $n = 0;
        $latest_list = [$key_latest];
        $hot_list = [$key_hot];
        $data_list = [];

        $list = $this->fetchAll();
        foreach ($list as $row) {

            $uid = $row['uid'];

            $user = $ds_user->getUser($uid);

            $row['cover'] = \Live\Lib\Utility::imageLarge($row['cover']);
            $row['nickname'] = $user['nickname'];
            $row['zodiac'] = (string)$user['zodiac'];

            $latest_list[] = ++$n;
            $latest_list[] = $uid;

            $s = $ds_rank->getRecentIncome($uid) or $s = $n;
            $hot_list[] = $s;
            $hot_list[] = $uid;

            $data_list[$uid] = \msgpack_pack($row);
        }

        //缓存数据
        $this->cache->link->hMset($key_data, $data_list);
        $this->cache->expire($key_data, $expire);//3600

        //缓存热门列表
        call_user_func_array([$this->cache->link, 'zAdd'], $hot_list);
        $this->cache->expire($key_hot, $expire);//3600

        //缓存最近列表
        call_user_func_array([$this->cache->link, 'zAdd'], $latest_list);
        $this->cache->expire($key_latest, $expire);//3600

        return $latest_list;
    }

    public function updateLive($uid, $new_data)
    {
        $data = $this->getLive($uid);

        if ($data) {
            $ret = $this->hashTable($uid)->where('uid', $uid)->update($new_data);
        } else {
            $ret = $this->hashTable($uid)->insert($new_data + ['uid' => $uid]);
        }

        if (!$ret)
            return Response::msg('开播失败', 1025);

        return $data;
    }

    public function stop($uid)
    {
        return $this->hashTable($uid)->where('uid = ? AND status = 1', $uid)->update(['status' => 0]);
    }

    public function getLivingUrl($uid)
    {
        $live = $this->getLive($uid);

        return $live['status'] > 0 ? $live['play_url'] : '';
    }

    public function getLive($uid, $type = 'all')
    {
        $live = $this->getWithCache($this->key_live . $uid, function () use ($uid) {
            return $this->select('uid,title,city,cover,play_url,hls_url,ts,third,status')->orderBy('ts DESC')->hashTable($uid)->where('uid', $uid)->fetch();
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

    public function getLiveOfFollow($uid, $start)
    {
        $ret = [];
        if ($start > 0)
            return $ret;

        $follow = (new Follow())->getList($uid, 0, 500);
        $follow = $follow[0];
        if ($follow) {
            $ret = $this->getList($this->key_list_latest, $start, 0, function ($list) use ($follow) {
                $nl = [];

                foreach ($follow as $_ => $uid) {
                    $score = &$list[$uid];
                    if ($score)
                        $nl[$uid] = $score;
                }

                return $nl;
            });
        }

        return $ret;


//        return $this->getLatest($start, $uid, function () use ($uid) {
//            $follow = (new Follow())->getList($uid, 0, 500);
//            $follow = $follow[0];
//            if ($follow) {
//                $this->where('uid', 'IN', array_keys($follow));
//                return $this->fetchAll();
//            }
//
//            return [];
//        });
    }
}