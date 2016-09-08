<?php

namespace Live\Controller;

use Live\Response;

class Live extends Basic
{
    public function follow()
    {
        $data = parent::getValidator()->required('token')->ge('key', 0)->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];

        $start = (int)$data['key'];
        $list = (new \Live\Database\Live())->getLiveOfFollow($token_uid, $start);

        return Response::data(['list' => $list]);
    }
}