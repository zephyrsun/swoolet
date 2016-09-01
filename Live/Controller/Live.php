<?php

namespace Live\Controller;

use Live\Response;

class Live extends Basic
{
    public function follow()
    {
        $data = parent::getValidator()->required('token')->le('key', 0)->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];
        $token_uid = 2;

        $start_id = (int)$data['key'];
        $raw = (new \Live\Database\Live())->getLiveOfFollow($token_uid, $start_id);

        $list = [];
        foreach ($raw as $data => $key) {
            $list[] = \msgpack_unpack($data) + ['key' => $key];
        }

        return Response::data(['list' => $list]);
    }
}