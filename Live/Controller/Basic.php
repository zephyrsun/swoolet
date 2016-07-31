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

        App::response(json_encode($data, \JSON_UNESCAPED_UNICODE));
    }
}

class Validator extends \Swoolet\Lib\Validator
{
    public function getResult()
    {
        if (!$result = parent::getResult())
            Response::msg("参数错误：" . $this->getFirstError(), 402);

        return $result;
    }
}