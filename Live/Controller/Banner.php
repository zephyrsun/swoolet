<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;

class Banner extends Basic
{
    public function index()
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $list = (new \Live\Database\Banner())->getAll(true);
        $list = array_values($list);

        Response::data(['list' => $list]);
    }

    public function view()
    {
        $data = parent::getValidator()->required('token')->required('id')->getResult();
        if (!$data)
            return $data;

        $data = (new \Live\Database\Banner())->getBanner($data['id']);

        //  Response::data(['banner' => $data]);
    }
}