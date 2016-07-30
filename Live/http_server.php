<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

class Http extends \Swoolet\Http
{
    public function parseData($request)
    {
        $_POST = $request->post ? $request->post : [];

        parent::parseData($request);
    }
}

$app = Http::createServer('Live', 'dev');
$app->run(':80');