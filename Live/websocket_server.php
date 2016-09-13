<?php

// php websocket_server.php start dev

if (!$env = &$argv[2]) {
    echo 'Please input ENV' . PHP_EOL;
    return;
}

$base_dir = \dirname(__DIR__);

include $base_dir . '/Swoolet/App.php';

class Server extends \Swoolet\WebSocket
{
    static public $msg = '';
    /**
     * @var \Live\Lib\Conn
     */
    static public $conn;

    public function init($sw)
    {
        self::$conn = \Live\Lib\Conn::getInstance()->process($sw);

        //self::$conn->subRoom($sw);
    }

    public function onWorkerStart($sw, $worker_id)
    {
        parent::onWorkerStart($sw, $worker_id);

        //self::$conn = \Live\Lib\Conn::getInstance();
        //self::$conn->onWorkerStart($sw, $worker_id);
    }

    public function onWorkerStop($sw, $worker_id)
    {
        parent::onWorkerStop($sw, $worker_id);

        //self::$conn->onWorkerStop($sw, $worker_id);
    }

    public function onOpen($sw, $request)
    {
        $fd = $request->fd;

        //没有成功登陆,踢出去
        swoole_timer_after(1500, function () use ($sw, $fd) {
            if (!self::$conn->getConn($fd)) {
                $this->sw->close($fd);
            }
        });
    }

    public function onClose($sw, $fd, $from_id)
    {
        self::$conn->leave($fd);
    }

    /**
     * ['m' => 'login', 'uid' => 1, 'token' => 'xxxxxx']
     *
     * @param swoole_websocket_frame $frame
     */
    public function onMessage($sw, $frame)
    {
        if (!$frame->finish)
            return;

        self::$msg = '';

        $_POST = \json_decode($frame->data, true);
        if ($_POST && $uri = &$_POST['m']) {

            \Swoolet\log($uri, $frame->fd);

            $this->callRequest($uri, $frame);
        }

        $this->response($frame->fd, self::$msg);
    }
}

\Swoolet\Router::$delimiter = '_';

$cfg = [
    'worker_num' => 8,
//        'reactor_num' => 1,
    'dispatch_mode' => 2,
    'max_conn' => 10000,
    'max_request' => 0,
    //'task_worker_num' => 1,

    'open_tcp_keepalive' => 1,
    'tcp_keepidle' => 60,
    'tcp_keepinterval' => 60,
    'tcp_keepcount' => 5,
    'daemonize' => 1,
    'heartbeat_check_interval' => 60,
    'heartbeat_idle_time' => 600,
];

if ($env == 'dev') {
    //$cfg['max_request'] = 0;
    $cfg['worker_num'] = 2;
    $cfg['max_conn'] = 500;
    $cfg['daemonize'] = 0;
}

Server::createServer('Live', $env)->run(':9502', $cfg);