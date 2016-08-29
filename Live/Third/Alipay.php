<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

include BASE_DIR . 'Live/Third/alipay/lib/alipay_submit.class.php';

class Alipay
{
    public $config = [];

    public function __construct()
    {
        $this->config = include BASE_DIR . 'Live/Third/alipay/alipay.camhow.php';
    }

    public function createOrder($trade_no, $coin, $total_fee, $gift_id)
    {
        $subject = "充值{$coin}看币";

        $body = "充值看币_{$coin}_{$gift_id}";

        $config = $this->config;

        $param = [
            'service' => $config['service'],
            'partner' => $config['partner'],
            'seller_id' => $config['seller_id'],
            'payment_type' => $config['payment_type'],
            'notify_url' => $config['notify_url'],
            'return_url' => $config['return_url'],

            'anti_phishing_key' => $config['anti_phishing_key'],
            'exter_invoke_ip' => $config['exter_invoke_ip'],
            'out_trade_no' => $trade_no,
            'total_fee' => $total_fee,
            'subject' => $subject,
            'body' => $body,
            '_input_charset' => $config['input_charset'],

        ];

        $alipaySubmit = new \AlipaySubmit($this->config);
        return $alipaySubmit->buildRequestPara($param);
    }
}