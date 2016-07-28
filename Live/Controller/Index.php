<?php

namespace Live\Controller;

use Live\Response;

class Index extends Basic
{
    public function index()
    {
        Response::msg('Hello world!');
    }
}