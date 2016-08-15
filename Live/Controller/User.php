<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;


use Live\Database\Balance;
use Live\Database\Fan;
use Live\Database\Follow;
use Live\Database\Income;
use Live\Database\UserLevel;
use Live\Response;

class User extends Basic
{
    public function getUserInfo()
    {
        $data = parent::getValidator()->required('token')->ge('uid', 1)->getResult();
        if (!$data)
            return $data;

        $uid = $data['uid'];

        $user = (new \Live\Database\User())->getUser($uid);

        $user += [
            'lv' => (new UserLevel())->getLv($uid),
            'income' => (new Income())->getIncome($uid),
            'sent' => (new Balance())->getSent($uid),
            'follow' => (new Follow())->getCount($uid),
            'fan' => (new Fan())->getCount($uid),
        ];

        return Response::data(['user' => $user]);
    }

    /**
     * 关注
     * @param $request
     * @return array
     */
    public function follow($request)
    {
        $data = parent::getValidator()->required('token')->ge('uid', 1)->getResult();
        if (!$data)
            return $data;

        $ret = (new Fan())->follow($data['token_uid'], $data['uid']);

        return Response::msg('关注成功');
    }

    public function unfollow($request)
    {
        $data = parent::getValidator()->required('token')->ge('uid', 1)->getResult();
        if (!$data)
            return $data;

        $ret = (new Fan())->unfollow($data['token_uid'], $data['uid']);

        return Response::msg('取消关注成功');
    }
}