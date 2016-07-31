<?php

namespace Live\Controller;


use Live\Database\User;
use Live\Redis\Common;
use Live\Response;

class Login extends Basic
{
    public $sms_key = 'sms:';

    public function third()
    {
        $data = parent::getValidator()->required('username')->getResult();
        if (!$data)
            return;
    }

    public function mobile()
    {
        $data = parent::getValidator()->mobileNumberCN('mobile')->required('code')->getResult();
        if (!$data)
            return;

        $mobile = $data['mobile'];
        $code = $data['code'];

        $r_code = (new Common())->get($this->sms_key . $mobile);

        if ($r_code != $code)
            return Response::msg('验证码错误', 1001);

        $user = (new User())->login($mobile, '');

        Response::data([
            'user' => $user,
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

        if (!$common->set($this->sms_key . $mobile, $code, $timeout))
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