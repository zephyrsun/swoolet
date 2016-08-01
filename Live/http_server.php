<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

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

    public function parseData($request)
    {
        $_POST = $request->post ? $request->post : [];

        //header('Content-type: application/json');
        parent::parseData($request);
    }
}

$app = Server::createServer('Live', 'dev');
$app->run(':80');