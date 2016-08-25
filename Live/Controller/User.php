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
        $token_uid = $data['token_uid'];

        $user = (new \Live\Database\User())->getUser($uid);

        $db_fan = new Fan();

        if ($uid == $data['token_uid']) {
            $is_follow = false;
        } else {
            $is_follow = $db_fan->isFollow($token_uid, $uid);
        }

        $user += [
            'lv' => (new UserLevel())->getLv($uid),
            'income' => (new Income())->getIncome($uid),
            'sent' => (new Balance())->get($uid, 'sent'),
            'follow' => (new Follow())->getCount($uid),
            'fan' => $db_fan->getCount($uid),
            'is_follow' => $is_follow,
        ];

        return Response::data(['user' => $user]);
    }

    public function updateUserInfo()
    {
        $data = parent::getValidator()->required('token')
            ->length('nickname', 1, 8, false)
            ->length('sex', 1, 1, false)
            ->between('height', 150, 240, false)
            ->required('birthday', false)
            ->required('zodiac', false)
            ->lengthLE('sign', 50, false)
            ->lengthLE('city', 10, false)
            ->getResult();
        if (!$data)
            return $data;

        $uid = $data['token_uid'];

        $user_fields = ['nickname', 'sex', 'height', 'birthday', 'zodiac', 'sign', 'city'];

        $data = [];
        foreach ($_POST as $k => $v) {
            if (in_array($k, $user_fields, true)) {
                $data[$k] = $v;
            }
        }

        $db_user = new \Live\Database\User();

        if ($data)
            $ret = $db_user->updateUser($uid, $data);
        else
            $ret = 0;

        if ($ret)
            return Response::data(['user' => $db_user->getUser($uid)]);

        return Response::msg('更新失败', 1041);
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