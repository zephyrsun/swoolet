<?php

namespace Live\Controller;

use Live\Database\UserLevel;
use Live\Response;
use Swoolet\App;

class Index extends Basic
{
    public function index()
    {
        return Response::msg('Hello world!');
    }

    public function _test()
    {
        //$q = (new \Live\Third\Pili())->stop('test_1_1470301542', '1470301542', \Swoolet\App::$ts);

        (new UserLevel())->add(1,1);
    }
}