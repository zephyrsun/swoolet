<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

use Swoolet\App;

class Server extends \Swoolet\Http
{
    /**
     * @var \Live\Lib\Conn
     */
    static public $conn;

    public function onWorkerStart($sw, $worker_id)
    {
        parent::onWorkerStart($sw, $worker_id);
        $this->content_type = 'Content-type: application/json';
        //echo 'onStart' . PHP_EOL;
    }

    public function onRequest($request, $response)
    {
        $this->response = $response;

        if ($request->server['path_info'] == '/favicon.ico')
            return $this->response('');

        $_POST = $request->post ? $request->post : [];

        //header('Content-type: application/json');
        App::callRequest($request->server['path_info'], $request, $response);
    }
}

$app = Server::createServer('Live', 'dev');
$setting = include './Config/swoole_setting.php';
$app->run(':80', $setting);