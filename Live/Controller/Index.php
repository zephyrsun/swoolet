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

    public function userAgreement()
    {
    }
}