<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

class JPush
{
    public $curl, $url = 'https://api.jpush.cn/v3';

    public function __construct()
    {
        $cfg = \Swoolet\App::getConfig('jpush');

        $this->curl = new \Swoolet\Lib\CURL([
            CURLOPT_USERPWD => $cfg['key'] . ':' . $cfg['secret']
        ]);
    }

    public function push($msg, $audience, $extras = [])
    {
        $audience == 'all' or $audience = ['alias' => [$audience]];//此时$audience为已经alias过的uid

        $json = \json_encode([
            'platform' => 'all',
            'audience' => $audience,
            'notification' => [
                'alert' => $msg,
                'extras' => $extras,
            ],
            'ios' => [
                'badge' => '+1',
                //'sound' => 'sound.caf',
            ],
            'options' => [
                'apns_production' => \Live\isProduction(),
            ]
        ], \JSON_UNESCAPED_UNICODE);

//        App::$server->sw->task('task_push', -1, function () use ($json) {
//            return $this->curl->post("{$this->url}/push", $json);
//        });
        return $this->curl->post("{$this->url}/push", $json);
    }

    public function bind($registration_id, $uid)
    {
//        App::$server->sw->task('task_push', -1, function () use ($registration_id, $uid) {
//            return $this->curl->post("{$this->url}/devices/{$registration_id}", [
//                'alias' => $uid,
//            ]);
//        });

        return $this->curl->post("{$this->url}/devices/{$registration_id}", [
            'alias' => $uid,
        ]);
    }
}