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

        $start_id = (int)$data['key'];

        $raw = (new \Live\Database\Live())->getLiveList($start_id);

        $list = [];
        foreach ($raw as $data => $key) {
            $list[] = \msgpack_unpack($data) + ['key' => $key];
        }

        return Response::data(['list' => $list]);
    }
}