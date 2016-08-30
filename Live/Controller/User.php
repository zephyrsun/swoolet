<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;


use Live\Database\Album;
use Live\Database\Balance;
use Live\Database\Fan;
use Live\Database\Follow;
use Live\Database\Income;
use Live\Database\Replay;
use Live\Database\RoomAdmin;
use Live\Database\UserLevel;
use Live\Response;

class User extends Basic
{
    public function getUserInfo($request)
    {
        $data = parent::getValidator()->required('token')->ge('uid', 1)->ge('room_id', 1, false)->getResult();
        if (!$data)
            return $data;

        $uid = $data['uid'];
        $token_uid = $data['token_uid'];

        $user = (new \Live\Database\User())->getUserInfo($uid, $token_uid);

        if ($room_id = &$data['room_id']) {
            $user['is_admin'] = (new RoomAdmin())->isAdmin($room_id, $uid);
        }

        return Response::data(['user' => $user]);
    }

    public function updateUserInfo($request)
    {
        $data = parent::getValidator()->required('token')
            ->length('nickname', 1, 20, false)
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

    public function home($request)
    {
        $data = parent::getValidator()->required('token')->ge('uid', 1)->getResult();
        if (!$data)
            return $data;

        $uid = $data['uid'];
        $token_uid = $data['token_uid'];

        $ds_user = new \Live\Database\User();

        $user = $ds_user->getUserInfo($uid, $token_uid);

        //访问记录
        $ds_user->addVisit($uid, $token_uid);

        $data = [
            'user' => $user,
            'visit' => $ds_user->getVisit($uid, 0, 5),
        ];

        $data += (new Album())->getList($uid, 0, 7);
        $data += (new Replay())->getList($uid, 0, 7);

        return Response::data($data);
    }

    public function getVisit($request)
    {
        $data = parent::getValidator()->required('token')->ge('uid', 1)->ge('key', 0)->getResult();
        if (!$data)
            return $data;

        $uid = $data['uid'];

        $start = (int)$data['key'];

        $visit = (new \Live\Database\User())->getVisit($uid, $start, 20);

        return Response::data([
            'list' => $visit,
        ]);
    }

    public function getAlbum($request)
    {
        $data = parent::getValidator()->required('token')->ge('uid', 1)->ge('key', 0)->getResult();
        if (!$data)
            return $data;

        $uid = $data['uid'];

        $start = (int)$data['key'];

        $album = (new Album())->getList($uid, $start, 20);

        return Response::data($album);
    }

    public function getReplay($request)
    {
        $data = parent::getValidator()->required('token')->ge('uid', 1)->ge('key', 0)->getResult();
        if (!$data)
            return $data;

        $uid = $data['uid'];

        $start = (int)$data['key'];

        $replay = (new Replay())->getList($uid, $start, 20);

        return Response::data($replay);
    }
}