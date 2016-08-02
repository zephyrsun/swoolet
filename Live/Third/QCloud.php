<?php

namespace Live\Third;

include BASE_DIR . 'Live/Third/qcloud/src/QcloudApi/QcloudApi.php';

class QCloud
{

    public function __construct()
    {
        $config = [
            'SecretId' => '1105419096',
            'SecretKey' => '8hiSS46eA7Oarg5r',
            'RequestMethod' => 'GET',
            //'DefaultRegion' => 'bj',
        ];

        $live = \QcloudApi::load(\QcloudApi::MODULE_LIVE, $config);

        $a = $live->CreateLVBChannel();

        if ($a === false) {
            $error = $live->getError();
            echo "Error code:" . $error->getCode() . ".\n";
            echo "message:" . $error->getMessage() . ".\n";
            echo "ext:" . var_export($error->getExt(), true) . ".\n";
        } else {
            var_dump($a);
        }

        echo "\nRequest :" . $live->getLastRequest();
        echo "\nResponse :" . $live->getLastResponse();
        echo "\n";
    }

}