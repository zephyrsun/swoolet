<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

use Swoolet\Lib\CURL;

include BASE_DIR . 'Live/Third/pili/Pili.php';

class JPush
{
    const APP_KEY = '118a3ec296f6193665bdf95c';
    const MASTER_SECRET = 'f9c3c00704c1924d1ff62844';

    public $option = [], $curl, $url = 'https://api.jpush.cn/v3';

    public function __construct()
    {
        $this->option = [
            CURLOPT_USERPWD => self::APP_KEY . ':' . self::MASTER_SECRET
        ];

        $this->curl = new CURL();
    }

    public function push($msg, $audience)
    {
        if ($audience != 'all') {
            //此时$audience为uid
            $audience = [
                'alias' => [$audience]
            ];
        }

        $data = \json_encode([
            'platform' => 'all',
            'audience' => $audience,
            'notification' => [
                'alert' => $msg,
                'extras' => [],
            ],
            'ios' => [
                'badge' => '+1',
                //'sound' => 'sound.caf',
            ],
            'options' => [
                'apns_production' => false,
            ]
        ], \JSON_UNESCAPED_UNICODE);

        return $this->curl->post("{$this->url}/push", $data, $this->option);
    }

    public function bind($registration_id, $uid)
    {
        return $this->curl->post("{$this->url}/devices/{$registration_id}", [
            'alias' => $uid,
        ], $this->option);
    }
}