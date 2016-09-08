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
        $list = [];
        foreach ($raw as $uid => $key) {
            if ($user = $ds_user->getShowInfo($uid, 'lv')) {
                $user['key'] = $key;
                $list[] = $user;
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

        if (!(new Award())->couldAward($token_uid))
            return Response::msg('今天已经签到过了');

        $j = date('j', \Swoolet\App::$ts) % 2;

        $money = 0;
        $exp = 10;

        $ds_user = new \Live\Database\User();
        $user = $ds_user->getUser($token_uid);
        if ($ds_user->isVip($user)) {

            $money = 5;
            $exp = 20;

            if ($j) {
                //余数相同,暴击
                $exp = mt_rand(21, 50);
            }
            
        } elseif ($j) {
            //普通人暴击
            $exp = mt_rand(10, 20);
        }

        if ($money && !$ret = (new Balance())->add($token_uid, $money, 0))
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