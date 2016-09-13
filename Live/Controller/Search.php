<?php

namespace Live\Controller;

use Live\Response;

class Search extends Basic
{
    public function keyword()
    {
        $data = parent::getValidator()->required('token')->required('kw')->ge('key', 0)->getResult();
        if (!$data)
            return $data;

        $start = (int)$data['key'];

        $list = (new \Live\Lib\Elasticsearch())->search($data['kw'], $start);

        return Response::data(['list' => $list]);
    }

    public function userRecommend()
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $list = (new \Live\Redis\Award())->getVip();
        if (!$list) {
            $ds_live = new \Live\Database\Live();
            $list = $ds_live->cache->revRange($ds_live->key_list_hot, 0, 20, false);
        }

        $ds_user = (new \Live\Database\User());
        $ret = [];
        foreach ($list as $uid) {
            if ($user = $ds_user->getShowInfo($uid, 'lv'))
                $ret[] = $user;
        }

        return Response::data(['list' => $ret]);
    }
}