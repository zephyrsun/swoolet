<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;
use Live\Database\Banner as DataBanner;

class Banner extends Basic
{
    public function index()
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $list = (new DataBanner())->getAllBanner();
        $list = array_values($list);

        return Response::data(['list' => $list]);
    }

    public function view()
    {
        $data = parent::getValidator()->required('token')->required('id')->getResult();
        if (!$data)
            return $data;

        $data = (new DataBanner())->getBanner($data['id']);

        //  Response::data(['banner' => $data]);
    }

    public function splash()
    {
        $data = parent::getValidator()->required('pf')->required('ch')->getResult();
        if (!$data)
            return $data;

        $splash = (new DataBanner())->getSplash();
        if (!$splash) {
            $splash = ['img' => ''];
        }

        return Response::data($splash);
    }
}