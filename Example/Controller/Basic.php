<?php

namespace Example\Controller;


class Basic extends \Swoolet\Controller
{
}

namespace Example;

class Response
{
    static public function error($msg, $code)
    {
        self::ok(array('msg' => $msg), $code);
    }

    static public function ok(array $data = array(), $code = 0)
    {
        $data['c'] = $code;

        \Swoolet\App::response(json_encode($data));
    }
}