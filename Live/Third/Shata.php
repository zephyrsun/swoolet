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
    public function __construct()
    {
    }

    public function start($key)
    {
        $url = "rtmp://st-publish.camhow.com.cn/camhow/$key";

        $ep = json_encode([
            'sid' => $key,
            'app' => 'camhow',
            'instance' => $key,
            'code' => 'code10',
            'rtmp-push-url' => $url
        ]);

        $td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
        mcrypt_generic_init($td, 'shata123', $iv);
        $ep = mcrypt_generic($td, $ep);
        mcrypt_generic_deinit($td);

        $ep = urlencode(base64_encode($ep));

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