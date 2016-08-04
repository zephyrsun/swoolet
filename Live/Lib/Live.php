<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/4
 * Time: 下午3:56
 */

namespace Live\Lib;

class Live
{
    const STATUS_START = 1;
    const STATUS_STOP = 0;

    public $sdk;
    public $db;

    public $prefix = '';

    public function __construct()
    {
        $this->sdk = new \Live\Third\Pili();
        $this->db = new \Live\Database\Live();

        $this->prefix = defined('TEST') ? 'test_' : 'live_';
    }

    public function getKey($uid)
    {
        return "{$this->prefix}{$uid}";
    }

    public function start($uid)
    {

        $ret = $this->sdk->start($this->getKey($uid));
        $this->db->start($uid, $ret + [
                'status' => self::STATUS_START,
                'publish_ts' => \Swoolet\App::$ts,
            ]);

        return $ret;
    }

    public function stop($uid)
    {
        $this->db->stop($uid);

        $live_data = $this->db->get($uid);

        $stream_data = $this->sdk->stop($this->getKey($uid), $live_data['publis_ts'], \Swoolet\App::$ts);
        $data = ['duration' => 0];
        if ($stream_data) {
            $data = [
                'title' => $live_data['title'],
                'location' => $live_data['location'],
                'play_url' => $stream_data['play_url'],
                'duration' => $stream_data['duration'],
            ];

            (new \Live\Database\Replay())->saveReplay($uid, $data);
        }

        return $data['duration'];
    }

}