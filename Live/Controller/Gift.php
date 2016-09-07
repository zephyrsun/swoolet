<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;

class Gift extends Basic
{
    public function getGift()
    {
        $data = parent::getValidator()->required('v')->getResult();
        if (!$data)
            return $data;

        $list = (new \Live\Database\Gift())->getAll(10);

        Response::data(['list' => $list]);
    }
}