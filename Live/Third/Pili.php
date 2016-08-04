<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

include BASE_DIR . 'Live/Third/pili/Pili_v2.php';

class Pili
{
    const AK = 'qNag3DRwm0rr3SjKY4XiIcMYIqyw-zhFetgmKsco';
    const SK = '6wt1qX8e34HDMaop6hsfYBGiq-X32Tmf6k66VCW6';
    const HUB = 'camhow';

    const PUBLISH_DOMAIN = 'pili-publish.camhow.com.cn';

    public $hub;

    public function __construct()
    {
        $mac = new \Qiniu\Pili\Mac(self::AK, self::SK);
        $client = new \Qiniu\Pili\Client($mac);
        $this->hub = $client->hub(self::HUB);
    }

    public function start($key)
    {
        //$stream = $this->hub->stream($key);

        $this->hub->create($key);

        $publish_url = \Qiniu\Pili\RTMPPublishURL(self::PUBLISH_DOMAIN, self::HUB, $key, 3600, self::AK, self::SK);
        $play_url = \Qiniu\Pili\RTMPPlayURL(self::PUBLISH_DOMAIN, self::HUB, $key);
        $hls_url = \Qiniu\Pili\HLSPlayURL(self::PUBLISH_DOMAIN, self::HUB, $key);

        return [
            'publish_url' => $publish_url,
            'play_url' => $play_url,
            'hls_url' => $hls_url,
        ];
    }

    public function stop($key, $start_ts, $end_ts)
    {
        $stream = $this->hub->stream($key);

        try {
            $fname = $stream->save((int)$start_ts, (int)$end_ts);
        } catch (\Exception $e) {
            $fname = null;
        }

        if (!$fname)
            return $fname;

        return [
            'play_url' => '',
            'duration' => $end_ts - $start_ts,
        ];
    }
}