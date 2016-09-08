<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;

class Album extends Basic
{
    public function del()
    {
        $data = parent::getValidator()->required('token')->required('id')->getResult();
        if (!$data)
            return $data;

        $ret = (new \Live\Database\Album())->del($data['token_uid'], $data['id']);

        Response::msg('删除成功');
    }
}