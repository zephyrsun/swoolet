<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Database\Goods;
use Live\Database\MoneyLog;
use Live\Response;

class Alipay extends Basic
{
    public function notify()
    {
    }

    public function callback()
    {

    }

    public function createOrder()
    {
        $data = parent::getValidator()->required('token')->required('goods_id')->required('pf')->getResult();
        if (!$data)
            return $data;

        $info = (new MoneyLog())->addOrder($data['token_uid'], $data['goods_id'], $data['pf']);
        if (!$info)
            return $info;

        $param = (new \Live\Third\Alipay())->createOrder($info['trade_no'], $info['coin'], $info['money'], $info['id']);

        Response::data(['param' => $param]);
    }
}