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
        $data = parent::getValidator()->required('token')->required('pf')->ge('channel', 1)->ge('type', 1)->getResult();
        if (!$data)
            return $data;

        $pf = strtolower($data['pf']);

        $list = (new Goods())->getChannel($data['channel'], $data['type'], $pf);

        Response::data(['list' => $list]);
    }
}