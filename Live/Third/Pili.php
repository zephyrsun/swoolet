<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

use Swoolet\App;

include BASE_DIR . 'Live/Third/pili/Pili.php';

class Pili
{
    public $hub;

    public function __construct()
    {
        $cfg = App::getConfig('qiniu');

        $credentials = new \Qiniu\Credentials($cfg['key'], $cfg['secret']); # => Credentials Object
        $this->hub = new \Pili\Hub($credentials, $cfg['hub']); # => Hub Object
    }

    public function start($key, $stream_id)
    {
        try {
            $stream = $this->hub->getStream($stream_id);
            $stream->enable();
        } catch (\Exception $e) {
            $stream = $this->hub->createStream($key, null, 'static');
        }

        /*
        $result = @$this->hub->listStreams(null, 1, $key);
        $items = &$result['items'];
        if ($items && ($stream = \current($items)) && $key == $stream->title) {
            $stream->enable();
        } else {
            $stream = $this->hub->createStream($key, null, 'static');
        }
        */

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

    public function stop($key, $start_ts, $end_ts, $stream_id)
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