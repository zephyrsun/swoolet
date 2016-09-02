<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Database\Goods;
use Live\Response;

class Charge extends Basic
{
    public function getGoods()
    {
        $data = parent::getValidator()->required('token')->required('pf')->required('channel')->ge('type', 1)->getResult();
        if (!$data)
            return $data;

        $pf = strtolower($data['pf']);

        $list = (new Goods())->getList($pf, $data['type'], $data['channel'], true);

        Response::data(['list' => $list]);
    }
}