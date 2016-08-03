<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

use Swoolet\App;

class Server extends \Swoolet\Http
{
    public function onRequest($request, $response)
    {
        $this->response = $response;

        if ($request->server['path_info'] == '/favicon.ico')
            return $this->response('');

        $_POST = $request->post ? $request->post : [];

        //header('Content-type: application/json');
        App::callRequest($request->server['path_info'], $request);
    }
}

$app = Server::createServer('Live', 'dev');
$setting = include './Config/swoole_setting.php';
$app->run(':80', $setting);