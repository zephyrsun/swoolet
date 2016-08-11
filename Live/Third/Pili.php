<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

include BASE_DIR . 'Live/Third/pili/Pili.php';

class Pili
{
    const AK = 'uk_JgveWYYcNXE730vQdHyRaAV86DplixzERLRy-';
    const SK = 'EHNf0jpUcLa8iVRO47aL178lF_zcPnsEwTE4LD-c';
    const HUB = 'kanhao';

    public $hub;

    public function __construct()
    {
        $credentials = new \Qiniu\Credentials(self::AK, self::SK); #=> Credentials Object
        $this->hub = new \Pili\Hub($credentials, self::HUB); # => Hub Object
    }

    public function start($key)
    {
        //$stream = $this->hub->stream($key);

        $stream = $this->hub->createStream($key, null, 'static');

        $publish_url = $stream->rtmpPublishUrl();
        $play_url = $stream->rtmpLiveUrls()['ORIGIN'];
        $hls_url = $stream->hlsLiveUrls()['ORIGIN'];

        return [
            'publish_url' => $publish_url,
            'play_url' => $play_url,
            'hls_url' => $hls_url,
        ];
    }

    public function stop($key, $start_ts, $end_ts)
    {
        $stream = $this->hub->createStream($key, null, 'static');

        try {
            $ret = $stream->saveAs("{$key}_{$start_ts}.mp4", null, (int)$start_ts, (int)$end_ts);
        } catch (\Exception $e) {
            $ret = null;
        }

        if (!$ret)
            return $ret;

        return [
            'play_url' => $ret['targetUrl'],
            'hls_url' => $ret['url'],
            'duration' => $end_ts - $start_ts,
        ];
    }
}