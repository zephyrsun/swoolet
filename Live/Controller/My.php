<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Database\Fan;
use Live\Database\Follow;
use Live\Database\Live;
use Live\Response;

class My extends Basic
{
    public function castInfo()
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $uid = $data['token_uid'];

        //$user = (new \Live\Database\User())->getUser($uid);
        $user = [];

        $user['cover'] = '';
        if ($live = (new Live())->getLive($uid, 'all')) {
            $user['cover'] = $live['cover'];
        }

        Response::data(['user' => $user]);
    }

    public function info()
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $uid = $data['token_uid'];

        $user = (new User())->getUser($uid);

        Response::data(['user' => $user]);
    }

    public function follows($request, $modal = '')
    {
        $data = parent::getValidator()->required('token')->required('key', false)->getResult();
        if (!$data)
            return $data;

        if (!$modal)
            $modal = new Follow();

        $start_id = (int)$data['key'];

        list($raw) = $modal->getList($data['token_uid'], $start_id);
        $db_user = new User();
        $list = [];
        foreach ($raw as $uid => $key) {
            $list[] = $db_user->getShowInfo($uid, 'simple') + ['key' => $key];
        }

        Response::data([
            'list' => $list,
        ]);
    }

    public function fans($request)
    {
        $this->follows($request, new Fan());
    }
}