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
use Live\Database\Live;
use Live\Database\RoomAdmin;
use Live\Database\UserLevel;
use Live\Redis\Vip;
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

    public function follows($request, $modal = '')
    {
        $data = parent::getValidator()->required('token')->required('key', false)->getResult();
        if (!$data)
            return $data;

        if (!$modal)
            $modal = new Follow();

        $start_id = (int)$data['key'];

        list($raw) = $modal->getList($data['token_uid'], $start_id, 30);
        $ds_user = new \Live\Database\User();
        $ds_level = new UserLevel();
        $list = [];
        foreach ($raw as $uid => $key) {
            $user = $ds_user->getUser($uid);

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
        if ((new \Live\Database\User())->isVip($token_uid)) {

            $exp = 20;
            $ds_vip = new Vip();

            if ($ds_vip->couldAward($token_uid) && ($rest = $ds_vip->getWait($token_uid))) {
                //vip抽奖

                $j = date('j', \Swoolet\App::$ts);
                if ($j % 2 == $token_uid % 2) {
                    //余数相同,暴击
                    $money = mt_rand(1, $rest);

                    if ($money == 1 || $ds_vip->decrWait($token_uid, $money) < 0) {
                        $money = 2;
                        $ds_vip->delWait($token_uid);
                    } else {
                        $ds_vip->addAward($token_uid, $money);
                    }
                }
            }
        }

        if (!$ret = (new Balance())->add($token_uid, $money, 0))
            return $ret;

        if (!$ret = (new UserLevel())->add($token_uid, $exp))
            return $ret;

        Response::data([
            'money' => $money,
            'exp' => $exp,
        ]);
    }
}