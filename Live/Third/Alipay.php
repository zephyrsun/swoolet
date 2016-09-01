<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

use Live\Response;

include BASE_DIR . 'Live/Third/alipay/lib/alipay_submit.class.php';
include BASE_DIR . 'Live/Third/alipay/lib/alipay_notify.class.php';


class Alipay
{
    public $config = [];

    public function __construct()
    {
        $this->config = include BASE_DIR . 'Live/Third/alipay/alipay.camhow.php';

        $host = \Live\isProduction() ? 'http://api.camhow.com.cn' : 'http://test.camhow.com.cn';

        $this->config['notify_url'] = $host . $this->config['notify_url'];

        if ($this->config['return_url'])
            $this->config['return_url'] = $host . $this->config['return_url'];
    }

    public function createOrder($uid, $gift_id, $pf)
    {
        $info = (new \Live\Database\MoneyLog())->addOrder($uid, $gift_id, $pf);
        if (!$info)
            return $info;

        $trade_no = $info['trade_no'];
        $coin = $info['coin'];
        $total_fee = $info['money'];

        $subject = "充值{$coin}看币";

        $body = "用户_{$uid}_花费_{$total_fee}_充值_{$coin}";

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
            'it_b_pay' => '30m',
        ];

        //$param['return_url'] = '';

        $submit = new \AlipaySubmit($this->config);

        return $submit->buildRequestParaToString($param);
    }

    public function notify($data)
    {
        $_POST = $data;

        $notify = new \AlipayNotify($this->config);
        $result = $notify->verifyNotify();

        if ($result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

            //交易状态
            $trade_status = $data['trade_status'];

            if ($trade_status == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            } else if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //付款完成后，支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");

                $result = $this->finishOrder($data);
            }
        }

        return $result ? 'success' : 'fail';
    }

    private function finishOrder($data)
    {
        //商户订单号
        $id = substr($data['out_trade_no'], 10);

        //支付宝交易号
        $trade_no = $data['trade_no'];

        $ds_money_log = new \Live\Database\MoneyLog();
        $ret = ($ds_money_log)->updateOrder($id, $trade_no);
        if (!$ret)
            return true;

        list($_, $uid, $_, $charge, $_, $coin) = explode('_', $data['body']);

        return (new \Live\Database\Balance())->add($uid, $coin, $charge, false);
    }
}