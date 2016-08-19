<?php

namespace Live\Controller;

use Live\Response;

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