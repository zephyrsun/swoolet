<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

use Swoolet\App;

class Shata
{
    public $obs = false;

    public function __construct($obs = false)
    {
        $this->obs = $obs;
    }

    public function pkcs5pad($text, $size)
    {
        $pad = $size - (strlen($text) % $size);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * @param $key
     * @param bool $obs true, if use OBS
     * @return array
     */
    public function start($key)
    {
        $url = "rtmp://st-publish.camhow.com.cn/camhow/$key";

        $ep = json_encode([
            'sid' => $key,
            'app' => 'camhow',
            'instance' => $key,
            'code' => 'code10',
            // 'rtmp-push-url' => $url
        ]);;

        $size = \mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
        $ep = $this->pkcs5pad($ep, $size);
        $ep = mcrypt_encrypt(MCRYPT_DES, 'shata123', $ep, MCRYPT_MODE_ECB);
        $ep = rawurlencode(base64_encode($ep));
        if ($this->obs)
            $ep = rawurlencode($ep);

        $publish_url = "$url?sid=$key&ep=$ep";

        return [
            'publish_url' => $publish_url,
            'play_url' => "rtmp://st-live-rtmp.camhow.com.cn/camhow/$key",
            'hls_url' => "http://st-live-hls.camhow.com.cn/camhow/$key/index.m3u8",
            'third' => 0,
        ];
    }

    public function stop($key, $start_ts, $end_ts, $stream_id)
    {
        $start_time = date('YmdHis', substr($start_ts, 0, 10));
        $end_time = date('YmdHis', substr($end_ts, 0, 10));

        return $ret = [
            'play_url' => "http://st-live-hls.camhow.com.cn/camhow/$key/index.m3u8?starttime={$start_time}&endtime={$end_time}",
            'duration' => $end_ts - $start_ts,
        ];
    }
}