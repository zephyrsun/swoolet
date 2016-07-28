<?php

namespace Live\Controller;

class Basic extends \Swoolet\Controller
{
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

function getParams($callback)
{
    /**
     * @var \Swoolet\Lib\Validator $v
     */
    $v = App::getInstance('\Swoolet\Lib\Validator');

    $v->setData($_GET);

    $callback($v);

    if (!$result = $v->getResult())
        return Response::msg("参数错误：" . $v->getFirstError(), 402);

    return $result;
}