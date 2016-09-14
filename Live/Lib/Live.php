<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/4
 * Time: 下午3:56
 */

namespace Live\Lib;

use Live\Redis\Rank;
use Live\Response;

class Live
{
    const STATUS_START = 1;
    const STATUS_STOP = 0;

    public $sdk;
    public $db;

    public $prefix = '';

    public function __construct($sdk = '')
    {
        $this->sdk = $sdk ? $sdk : new \Live\Third\Pili();
        $this->db = new \Live\Database\Live();

        $this->prefix = \Live\isProduction() ? 'live-' : 'test-';
    }

    public function getKey($uid)
    {
        return $this->prefix . $uid;
        //return "{$this->prefix}{$uid}_" . \Swoolet\App::$ts;
    }

    public function start($uid, $title, $city, $user = [])
    {
        if (!$title) {
            $user or $user = (new \Live\Database\User())->getUser($uid);
            $n = array_rand(['花式', '热辣', '搞怪', '灵魂', '神秘', '魔性']);
            $title = "{$user['nickname']}的{$n}直播";
        }

        $ts = \Swoolet\App::$ts;

        $live_data = $this->db->getLive($uid, 'all');
        if ($live_data) {
            $third = $live_data['third'];
            $factor = ceil(($ts - $live_data['ts']) / 86400);
            if ($factor > 1) {
                (new \Live\Redis\Rank())->decrRecentIncome($uid, 1 - $factor / 10);
            }
        } else {
            $third = '';
        }

        $ret = $this->sdk->start($this->getKey($uid), $third);

        $ok = $this->db->updateLive($uid, $ret + [
                'status' => self::STATUS_START,
                'ts' => $ts,
                'title' => $title,
                'city' => \Live\Lib\Utility::generateCity($city),
            ]);

        if (!$ok)
            return $ok;

        (new Rank())->incrRoomUserNum($uid);

        return $ret;
    }

    public function stop($key)
    {
        $arr = explode('-', $key, 2);
        $uid = \end($arr);

        $ret = $this->db->stop($uid);
        if (!$ret)
            Response::msg('停播失败', 1052);

        \Server::$conn->stopRoom($uid, $uid);

        $live_data = $this->db->getLive($uid, 'all');

        $stream_data = $this->sdk->stop($key, $live_data['ts'], \Swoolet\App::$ts, $live_data['third']);

        //$data = ['duration' => 0];
        if ($stream_data) {
            $data = $stream_data + [
                    'title' => $live_data['title'],
                    'city' => $live_data['city'],
                    'cover' => $live_data['cover'],
                    'view_num' => (new Rank())->getRoomUserNum($uid),
                ];

            (new \Live\Database\Replay())->saveReplay($uid, $data);
        }

        return $stream_data;
    }

}