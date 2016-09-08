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
use Live\Database\RoomAdmin;
use Live\Database\UserLevel;
use Live\Redis\Award;
use Live\Response;
use Live\Redis\Common;

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

        if ($live = (new \Live\Database\Live())->getLive($uid, 'all')) {
            $user['cover'] = \Live\Lib\Utility::imageLarge($live['cover']);
        }

        Response::data(['user' => $user]);
    }

    public function follows($request, $modal = '')
    {
        $data = parent::getValidator()->required('token')->required('key', false)->getResult();
        if (!$data)
            return $data;

        if (!$modal)
            $modal = new Follow();

        $start = (int)$data['key'];

        list($raw) = $modal->getList($data['token_uid'], $start, 30);
        $ds_user = new \Live\Database\User();
        $ds_level = new UserLevel();
        $list = [];
        foreach ($raw as $uid => $key) {
            if ($user = $ds_user->getUser($uid)) {
                $list[] = [
                    'uid' => $user['uid'],
                    'nickname' => $user['nickname'],
                    'avatar' => $user['avatar'],
                    'zodiac' => $user['zodiac'],
                    'city' => $user['city'],
                    'key' => $key,
                    'lv' => $ds_level->getLv($uid),
                ];
            }
        }

        Response::data([
            'list' => $list,
        ]);
    }

    public function fans($request)
    {
        $this->follows($request, new Fan());
    }

    public function admins()
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];

        $list = (new RoomAdmin())->getRoomAdmin($token_uid);
        $ds_user = new \Live\Database\User();
        foreach ($list as &$uid) {
            $uid = $ds_user->getShowInfo($uid, 'lv');
        }

        Response::data(['list' => $list]);
    }

    public function checkIn()
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];

        $money = 2;
        $exp = 10;

        $ds_user = new \Live\Database\User();
        $user = $ds_user->getUser($token_uid);
        if ($ds_user->isVip($user)) {

            $exp = 20;
            $ds_vip = new Award();

            if ($ds_vip->couldAward($token_uid)) {
                //vip抽奖

                $j = date('j', \Swoolet\App::$ts);
                if ($j % 2 == $token_uid % 2) {
                    //余数相同,暴击
                    $money = mt_rand(1, 30);
                    if ($money > 10) {
                        $ds_vip->addRecommend($token_uid, "签到获得{$money}看币");
                    }
                } elseif ($j % 3 == $token_uid % 3) {
                    $exp = mt_rand(10, 50);
                    if ($exp > 20) {
                        $ds_vip->addRecommend($token_uid, "签到获得{$exp}经验");
                    }
                }
            }
        }

        if (!$ret = (new Balance())->add($token_uid, $money, 0))
            return $ret;

        if (!$ret = (new UserLevel())->add($token_uid, $exp))
            return $ret;

        return Response::data([
            'money' => $money,
            'exp' => $exp,
        ]);
    }

    public function bindMobile()
    {
        $data = parent::getValidator()->required('token')->mobileNumberCN('mobile')->required('code')->getResult();
        if (!$data)
            return $data;

        $mobile = $data['mobile'];
        $code = $data['code'];

        $r_code = (new Common())->get(Login::SMS_KEY . $mobile);
        if ($r_code != $code)
            return Response::msg('验证码错误', 1001);

        $token_uid = $data['token_uid'];

        $ret = (new \Live\Database\User())->updateUser($token_uid, [
            'mobile' => $mobile
        ]);

        if ($ret)
            return Response::msg('ok');

        return Response::msg('手机号绑定失败', 1046);
    }
}