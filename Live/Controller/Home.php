<?php

namespace Live\Controller;

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

        $start = (int)$data['key'];

        $ds = new \Live\Database\Live();

        $list = $ds->getList($ds->key_list_hot, $start);

        return Response::data(['list' => $list]);
    }

    public function latest()
    {
        $data = parent::getValidator()->required('token')->le('key', 0)->getResult();
        if (!$data)
            return $data;

        $start = (int)$data['key'];

        $ds = new \Live\Database\Live();

        $list = $ds->getList($ds->key_list_latest, $start);

        return Response::data(['list' => $list]);
    }
}