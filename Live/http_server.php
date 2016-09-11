<?php

// php websocket_server.php start dev

if (!$env = &$argv[2]) {
    echo 'Please input ENV' . PHP_EOL;
    return;
}

$base_dir = \dirname(__DIR__);

include $base_dir . '/Swoolet/App.php';

class Server extends \Swoolet\Http
{
    static public $msg = '';
    /**
     * @var \Live\Lib\Conn
     */
    static public $conn;

    public function onWorkerStart($sw, $worker_id)
    {
        self::$conn = \Live\Lib\Conn::getInstance();

        parent::onWorkerStart($sw, $worker_id);
    }

    public function onRequest($request, $response)
    {
        $this->resp = $response;

        self::$msg = '';
        $_POST = isset($request->post) ? $request->post : [];

        //\Swoolet\log($request->server['path_info'], $request->fd);

        $this->callRequest($request->server['path_info'], $request);
        $this->response($request->fd, self::$msg);
    }
}

Server::createServer('Live', $env)->run(':8090');