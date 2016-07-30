<?php

namespace Live\Controller;

use Live\Response;
use Swoolet\App;

class Index extends Basic
{
    public function index()
    {
        //Response::msg('Hello world!', -1);
        App::response('Hello world!');
    }
}