<?php

namespace Example\Controller;

use Example\Response;

class Index extends Basic
{

    public function index()
    {
        // echo 'Hello world!';

        Response::ok(['msg' => 'Hello. This is index()!']);
    }

    public function webSocket()
    {
        Response::ok($_GET);
    }
}