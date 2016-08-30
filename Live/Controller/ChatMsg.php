<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Lib\Conn;
use Live\Response;

class ChatMsg extends Basic
{
    public function get($request)
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $msg = (new \Live\Database\ChatMsg())->getMsg($data['token_uid']);

        $users = [];
        $ds_user = new \Live\Database\User();
        foreach ($msg as &$row) {

            $row['t'] = Conn::TYPE_CHAT;

            $uid = $row['from_uid'];

            $user = &$users[$uid] or $user = $ds_user->getShowInfo($uid, 'lv');

            $row['user'] = $user;

            unset($row['from_uid']);
        }

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