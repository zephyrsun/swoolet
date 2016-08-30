<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;

class Rank extends Basic
{
    public function roomSent()
    {
        $data = parent::getValidator()->required('token')->required('room_id')->getResult();
        if (!$data)
            return $data;


        Response::data([
            'rank' => (new \Live\Redis\Rank())->getRankInRoom($data['room_id'], 0)
        ]);
    }
}