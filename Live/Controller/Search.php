<?php

namespace Live\Controller;

use Live\Response;

class Search extends Basic
{
    public function user()
    {
        $data = parent::getValidator()->required('token')->required('kw')->le('key', 0)->getResult();
        if (!$data)
            return $data;

        $start = (int)$data['key'];

        $ds = new \Live\Database\Live();

        $list = $ds->getList($ds->key_list_hot, $start);

        return Response::data(['list' => $list]);
    }
}