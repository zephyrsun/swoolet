<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Database\Log;
use Live\Response;
use Swoolet\Lib\CURL;

class AppleIAP extends Basic
{
    const BUY_URL = 'https://buy.itunes.apple.com/verifyReceipt';
    const SANDBOX_URL = 'https://sandbox.itunes.apple.com/verifyReceipt';

    public function verifyReceipt($request)
    {
        $data = parent::getValidator()->required('token')->required('receipt')->getResult();
        if (!$data)
            return $data;

        $url = \Live\isProduction() ? self::BUY_URL : self::SANDBOX_URL;

        $curl = new CURL();
        $ret = $curl->post($url, $data['receipt']);
        $ret = json_decode($ret, true);

        if ($ret['status'] != 0)
            return Response::msg('购买失败', 1049);

        (new Log())->add($request, $ret);
    }
}