<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;

class Alipay extends Basic
{
    public function notify($request)
    {
        if (isset($request->post)) {
            (new \Live\Database\Log())->add($request, '');
            \Server::$msg = (new \Live\Third\Alipay())->notify($request->post);
        }
    }

    public function createOrder($request)
    {
        $data = parent::getValidator()->required('token')->required('goods_id')->required('pf')->getResult();
        if (!$data)
            return $data;

        $param = (new \Live\Third\Alipay())->createOrder($data['token_uid'], $data['goods_id'], $data['pf']);

        Response::data(['param' => $param]);
    }
}