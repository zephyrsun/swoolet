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

    public function init($sw)
    {
        self::$conn = \Live\Lib\Conn::getInstance();
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

$daemonize = $env != 'dev';

Server::createServer('Live', $env)->run(':8090', [
   'worker_num' => 8,
//        'reactor_num' => 1,
    'dispatch_mode' => 2,
    'max_request' => 0,

    'open_tcp_keepalive' => 1,
    'tcp_keepidle' => 60,
    'tcp_keepinterval' => 60,
    'tcp_keepcount' => 5,
    'daemonize' => $daemonize,
    'heartbeat_check_interval' => 60,
    'heartbeat_idle_time' => 600,
]);