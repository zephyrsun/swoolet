<?php

namespace Live\Controller;

use Live\Response;

class Search extends Basic
{
    public function keyword()
    {
        $data = parent::getValidator()->required('token')->required('kw')->ge('key', 0)->getResult();
        if (!$data)
            return $data;

        $start = (int)$data['key'];

        $list = (new \Live\Lib\Elasticsearch())->search($data['kw'], $start);

        return Response::data(['list' => $list]);
    }
}