<?php

namespace Live\Controller;

use Live\Database\Live;
use Live\Response;

class Home extends Basic
{
    public function __call($name, $arg)
    {
        $this->hot();
    }

    public function hot()
    {
        $data = parent::getValidator()->required('token')->le('key', 0)->getResult();
        if (!$data)
            return $data;

        $start_id = (int)$data['key'];

        $list = (new Live())->getHome($start_id);

        return Response::data(['list' => $list]);
    }
}