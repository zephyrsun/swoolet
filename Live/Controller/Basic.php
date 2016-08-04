<?php

namespace Live\Controller;

use \Swoolet\App;
use \Live\Validator;

class Basic extends \Swoolet\Controller
{
    public function getValidator()
    {
        return (new Validator($_POST));
    }
}

namespace Live;

use \Swoolet\App;
use Swoolet\Lib\Crypt;

class Response
{

    /**
     * @param $msg
     * @param $code
     * @return null
     */
    static public function msg($msg, $code = 0)
    {
        self::data(array('msg' => $msg), $code);

        return false;
    }

    /**
     * @param array $data
     * @param int $code
     * @return null
     */
    static public function data(array $data = array(), $code = 0)
    {
        $data['c'] = $code;

        \Server::$msg = json_encode($data, \JSON_UNESCAPED_UNICODE);

        return true;
    }
}

class Validator extends \Swoolet\Lib\Validator
{
    public function getResult()
    {
        if (!$data = parent::getResult())
            return Response::msg("参数错误：" . $this->getFirstError(), 402);

        if (isset($data['token'])) {

            $uid = Cookie::decrypt($data['token']);

            if ($uid > 0 && is_numeric($uid))
                $data['uid'] = $uid;
            else
                return Response::msg('TOKEN失效', 1012);

            //$data['uid'] = 1;
        }

        return $data;
    }
}

class Cookie
{
    public function __construct($request)
    {
        if ($request->cookie)
            $_COOKIE = $request->cookie;
    }

    static function decrypt($str)
    {
        $arr = explode('|', $str, 2);
        if (count($arr) != 2)
            return false;

        $cipher = new Crypt($arr[0]);
        return $cipher->decrypt($arr[1]);
    }

    static function encrypt($str)
    {
        $key = base_convert(\Swoolet\App::$ts, 10, 36);

        $cipher = new Crypt($key);
        return $key . '|' . $cipher->encrypt($str);
    }

    public function get($key)
    {
        $str = &$_COOKIE[$key];
        if ($str)
            return self::decrypt($str);

        return $str;
    }

    public function set($key, $str)
    {
        $expire = 86400 * 90 + \Swoolet\App::$ts;

        $_COOKIE[$key] = self::encrypt($str);

        return App::$server->response->cookie($key, $str, $expire, '/', '', false, false);
    }
}