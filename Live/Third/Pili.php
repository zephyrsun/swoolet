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
        $credentials = new \Qiniu\Credentials(self::AK, self::SK); # => Credentials Object
        $this->hub = new \Pili\Hub($credentials, self::HUB); # => Hub Object
    }

    public function start($key)
    {
        $result = @$this->hub->listStreams(null, 1, $key);
        $items = &$result['items'];
        if ($items && ($stream = \current($items)) && $key == $stream->title) {
            /**
             * @var \Pili\Stream $stream
             */
            $stream->enable();
        } else {
            $stream = $this->hub->createStream($key, null, 'static');
        }

        $publish_url = $stream->rtmpPublishUrl();
        $play_url = $stream->rtmpLiveUrls()['ORIGIN'];
        $hls_url = $stream->hlsLiveUrls()['ORIGIN'];

        return [
            'publish_url' => $publish_url,
            'play_url' => $play_url,
            'hls_url' => $hls_url,
            'third' => $stream->id,
        ];
    }

    public function stop($key, $stream_id, $start_ts, $end_ts)
    {
        $stream = $this->hub->getStream($stream_id);
        $stream->disable();

        try {
            $ret = $stream->saveAs("{$key}_{$start_ts}.mp4", null, (int)$start_ts, (int)$end_ts);

            $ret = [
                'play_url' => $ret['url'],
                'duration' => $ret['duration'],
            ];

        } catch (\Exception $e) {
            $ret = false;
        }

        return $ret;
    }
}