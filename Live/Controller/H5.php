<?php

namespace Live\Controller;

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

        $goods = (new \Live\Database\Goods())->getList($pf, 2, $data['channel']);

        $this->view->assign([
            'pf' => $pf,
            'goods' => $goods,
            //'query' => http_build_query($request->get),
        ]);

        return $this->render('h5/buy_vip');
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