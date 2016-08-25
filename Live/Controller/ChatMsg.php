<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;

class ChatMsg extends Basic
{
    public function get($request)
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $msg = (new \Live\Database\ChatMsg())->getMsg($data['token_uid']);

        return Response::data([
            'msg' => $msg,
        ]);
    }

    public function markAsRead($request)
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        (new \Live\Database\ChatMsg())->markAsRead($data['token_uid']);

        return Response::msg('ok');
    }
}