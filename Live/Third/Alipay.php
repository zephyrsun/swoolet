<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

include BASE_DIR . 'Live/Third/alipay/lib/alipay_submit.class.php';
include BASE_DIR . 'Live/Third/alipay/lib/alipay_notify.class.php';


class Alipay
{
    public $config = [];

    public function __construct($request)
    {
        $this->config = include BASE_DIR . 'Live/Third/alipay/alipay.camhow.php';

        $host = 'http://' . $request->header['host'];

        $this->config['notify_url'] = $host . $this->config['notify_url'];
        $this->config['return_url'] = $host . $this->config['return_url'];
    }

    public function createOrder($trade_no, $coin, $total_fee, $gift_id)
    {
        $subject = "充值{$coin}看币";

        $body = "充值看币_{$coin}_{$gift_id}";

        $cfg = $this->config;

        $param = [
            'service' => $cfg['service'],
            'partner' => $cfg['partner'],
            'seller_id' => $cfg['seller_id'],
            'payment_type' => $cfg['payment_type'],
            'notify_url' => $cfg['notify_url'],
            'return_url' => $cfg['return_url'],

            'anti_phishing_key' => $cfg['anti_phishing_key'],
            'exter_invoke_ip' => $cfg['exter_invoke_ip'],
            'out_trade_no' => $trade_no,
            'total_fee' => $total_fee,
            'subject' => $subject,
            'body' => $body,
            '_input_charset' => $cfg['input_charset'],

        ];

        $submit = new \AlipaySubmit($this->config);
        echo $submit->buildRequestForm($param);
        return $submit->buildRequestPara($param);
    }

    public function callback($data)
    {
        $notify = new \AlipayNotify($this->config);
        $result = $notify->verifyReturn();
        if ($result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号
            $out_trade_no = $data['out_trade_no'];

            //支付宝交易号
            $trade_no = $data['trade_no'];

            //交易状态
            $trade_status = $data['trade_status'];

            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
            } else {
                echo "trade_status=" . $trade_status;
            }

            echo "验证成功<br />";

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "验证失败";
        }
    }
}