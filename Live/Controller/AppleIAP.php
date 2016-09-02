<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Database\Balance;
use Live\Response;

class AppleIAP extends Basic
{
    const BUY_URL = 'https://buy.itunes.apple.com/verifyReceipt';
    const SANDBOX_URL = 'https://sandbox.itunes.apple.com/verifyReceipt';

    public function verifyReceipt($request)
    {
        $data = parent::getValidator()->required('token')->required('channel')->required('receipt')->required('pid')->getResult();
        if (!$data)
            return $data;

        $url = \Live\isProduction() ? self::BUY_URL : self::SANDBOX_URL;

        $receipt = $data['receipt'];

        $ret = (new \Live\Redis\Common())->add('iap:' . $receipt, 1, 86400 * 30);
        if (!$ret)
            return Response::msg('receipt已过期', 1051);

        $json = json_encode([
            'receipt-data' => $receipt
        ]);

        $curl = new \Swoolet\Lib\CURL();
        $ret = $curl->post($url, $json);

        $ret = json_decode($ret, true);
        if ($ret['status'] != 0)
            return Response::msg('购买失败', 1049);

        $product_id = $ret['receipt']['in_app'][0]['product_id'];
        $goods_id = ltrim($product_id, $data['channel']);
        if (!$goods_id)
            return Response::msg('参数错误', 1050);

        $ret = (new Balance())->addByGoods($data['token_uid'], $goods_id, 'ios');
        if ($ret)
            return Response::msg('ok');

        return $ret;
    }
}