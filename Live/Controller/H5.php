<?php

namespace Live\Controller;

use Live\Lib\Utility;
use Live\Response;

class H5 extends Basic
{
    public $view;

    public function __construct()
    {
        $this->view = new \Swoolet\View\Basic();
    }

    public function requestValidator($request)
    {
        $data = isset($request->get) ? $request->get : [];
        return new \Live\Validator($data);
    }

    public function render($tpl)
    {
        return \Server::$msg = $this->view->fetch($tpl);
    }

    public function buyGoods($request)
    {
        $data = $this->requestValidator($request)->required('token')->required('pf')->required('channel')->getResult();
        if (!$data)
            return $data;

        $pf = strtolower($data['pf']);

        $goods = (new \Live\Database\Goods())->getList($pf, 1, $data['channel']);

        $token_uid = $data['token_uid'];
        $balance = (new \Live\Database\Balance())->get($token_uid, 'balance');

        // $user = (new \Live\Database\User())->getShowInfo($token_uid, 'lv');

        $user = (new \Live\Database\UserLevel())->getLvAndExp($token_uid);

        $this->view->assign([
            'goods' => $goods,
            'balance' => $balance,
            'user' => $user,
            'pf' => $pf,
            'query' => http_build_query($request->get),
        ]);

        return $this->render('h5/buy_goods');
    }

    public function buyVip($request)
    {
        $data = $this->requestValidator($request)->required('token')->required('pf')->required('channel')->getResult();
        if (!$data)
            return $data;

        $pf = strtolower($data['pf']);
        $token_uid = $data['token_uid'];

        $goods = (new \Live\Database\Goods())->getList($pf, 2, $data['channel']);

        $this->view->assign([
            'pf' => $pf,
            'goods' => $goods,
            'uid' => $token_uid,
            //'query' => http_build_query($request->get),
        ]);

        return $this->render('h5/buy_vip');
    }

    public function buySuccess($request)
    {
        $data = $this->requestValidator($request)->required('token')->required('pf')->required('channel')->required('id')->getResult();
        if (!$data)
            return $data;

        $id = ltrim($data['id'], $data['channel']);

        $token_uid = $data['token_uid'];
        $pf = strtolower($data['pf']);

        $goods = (new \Live\Database\Goods())->getGoods($id, $pf);

        $this->view->assign([
            'pf' => $pf,
            'goods' => $goods,
            'exp' => (new \Live\Database\UserLevel())->getExp($token_uid),
            'coin' => (new \Live\Database\Balance())->get($token_uid, 'balance'),
        ]);

        return $this->render('h5/buy_success');
    }

    public function live($request)
    {
        $data = $this->requestValidator($request)->required('id')->ge('ts', 1)->getResult();
        if (!$data)
            return $data;

        $uid = $data['id'];
        $ts = $data['ts'];

        $live = (new \Live\Database\Live())->getLive($uid);
        $user = (new \Live\Database\User())->getUser($uid);

        $user['avatar'] = Utility::imageSmall($user['avatar']);

        $video = [];

        if ($live['status']) {
            $video['title'] = $live['title'];
            $video['play_url'] = $live['play_url'];
            $video['cover'] = $live['cover'];
        } else {
            $last_row = [];

            $list = (new \Live\Database\Replay())->getList($uid, 0, 10);
            foreach ($list as $row) {
                if ($ts > $row['ts']) {
                    $video['title'] = $last_row['title'];
                    $video['play_url'] = $last_row['play_url'];
                    $video['cover'] = Utility::imageLarge($last_row['cover']);
                    break;
                }

                $last_row = $row;
            }
        }

        if (!$data) {
            //回放
            $video['title'] = '';
            $video['play_url'] = '';
        }

        $this->view->assign([
            'video' => $video,
            'user' => $user,
        ]);

        return $this->render('h5/live');
    }

    public function level($request)
    {
        $data = $this->requestValidator($request)->required('token')->required('pf')->getResult();
        if (!$data)
            return $data;

        return Response::msg('等级');

        $pf = strtolower($data['pf']);

        $this->view->assign([
            'pf' => $pf,
            //'query' => http_build_query($request->get),
        ]);

        return $this->render('h5/buy_vip');
    }

    public function about($request)
    {
        $data = $this->requestValidator($request)->required('token')->required('pf')->getResult();
        if (!$data)
            return $data;

        return Response::msg('关于');
    }

    public function help($request)
    {
        $data = $this->requestValidator($request)->required('token')->required('pf')->getResult();
        if (!$data)
            return $data;

        return Response::msg('帮助');
    }
}