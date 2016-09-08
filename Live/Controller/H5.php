<?php

namespace Live\Controller;

class H5 extends Basic
{
    public $view;

    public function __construct()
    {
        $this->view = new \Swoolet\View\Basic();
    }

    public function render($tpl)
    {
        return \Server::$msg = $this->view->fetch($tpl);
    }

    public function buy($request)
    {
        return $this->render('h5/buy');
    }
}