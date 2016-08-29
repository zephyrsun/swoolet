<?php

namespace Live\Controller;

use Live\Cookie;
use Live\Redis\Common;
use Live\Response;

class Login extends Basic
{
    const SMS_KEY = 'sms:';

    public function third()
    {
        $data = parent::getValidator()->required('username')->getResult();
        if (!$data)
            return;
    }

    public function mobile($request)
    {
        $data = parent::getValidator()->mobileNumberCN('mobile')->required('code')->lengthLE('city', 10)->getResult();
        if (!$data)
            return;

        $mobile = $data['mobile'];
        $code = $data['code'];

        $r_code = (new Common())->get(self::SMS_KEY . $mobile);
        if ($r_code != $code)
            return Response::msg('验证码错误', 1001);

        $db_user = new \Live\Database\User();

        $user = $db_user->login($db_user::PF_MOBILE, $mobile, [
            'city' => $data['city'],
            'nickname' => '手机尾号' . substr($mobile, -4),
            //'mobile' => $mobile,
        ]);

        Response::data([
            'user' => $user,
            'full' => $user['birthday'] != '0000-00-00',
            'token' => Cookie::encrypt($user['uid']),
        ]);
    }

    public function sendSms()
    {
        $data = parent::getValidator()->mobileNumberCN('mobile')->getResult();
        if (!$data)
            return;

        $mobile = $data['mobile'];

        $code = '123456';
        $timeout = 300;

        $common = new Common();

        if (!$common->set(self::SMS_KEY . $mobile, $code, $timeout))
            return;

        Response::data([
            'code' => $code,
        ]);
    }

    public function updateAvatar()
    {
        $data = parent::getValidator()->required('avatar')->getResult();
        if (!$data)
            return;
    }
}