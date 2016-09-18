<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;


use Live\Database\Album;
use Live\Database\Fan;
use Live\Database\Replay;
use Live\Lib\Elasticsearch;
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

        $ra = new \Live\Database\RoomAdmin();
        if ($room_id = &$data['room_id']) {
            $user['is_admin'] = $ra->isAdmin($room_id, $uid);
        } elseif ($uid == $token_uid) {
            $user['admin'] = $ra->getCount($uid);
            $user['money'] = (new \Live\Database\Balance())->get($uid, 'balance');
        }

        return Response::data([
            'user' => $user,
        ]);
    }

    public function updateUserInfo($request)
    {
        $data = parent::getValidator()->required('token')
            ->length('nickname', 1, 12, false)
            ->length('sex', 1, 1, false)
            ->between('height', 150, 250, false)
            ->required('birthday', false)
            ->required('zodiac', false)
            ->lengthLE('sign', 50, false)
            ->lengthLE('city', 10, false)
            ->getResult();
        if (!$data)
            return $data;

        if ($data['zodiac']) {
            return Response::msg('星座不可修改');
        }

        $uid = $data['token_uid'];

        $user_fields = ['nickname', 'sex', 'height', 'birthday', 'sign', 'city'];

        $data = [];
        foreach ($_POST as $k => $v) {
            if (in_array($k, $user_fields, true)) {
                $data[$k] = $v;
            }
        }

        if ($data) {
            $ds_user = new \Live\Database\User();

            if (!$ret = $ds_user->limitUpdate('get', $uid, $data))
                return $ret;

            $ret = $ds_user->updateUser($uid, $data);
            if ($ret) {
                $ds_user->limitUpdate('add', $uid, $data);
                return Response::data(['user' => $ds_user->getUser($uid)]);
            }
        }

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
            'play_url' => (new \Live\Database\Live())->getLivingUrl($uid),
        ];

        $album = (new Album())->getList($uid, 0, 7);
        $replay = (new Replay())->getList($uid, 0, 7);

        return Response::data($data + $album + $replay);
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