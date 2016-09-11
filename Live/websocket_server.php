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
        self::$conn = \Live\Lib\Conn::getInstance();

        self::$conn->subRoom($sw);
    }

    public function onWorkerStart($sw, $worker_id)
    {
        parent::onWorkerStart($sw, $worker_id);

        self::$conn = \Live\Lib\Conn::getInstance();
        self::$conn->onWorkerStart($sw, $worker_id);
    }

    public function onWorkerStop($sw, $worker_id)
    {
        parent::onWorkerStop($sw, $worker_id);

        self::$conn->onWorkerStop($sw, $worker_id);
    }

    public function onOpen($sw, $request)
    {
        $fd = $request->fd;

        //没有成功登陆,踢出去
        swoole_timer_after(1500, function () use ($sw, $fd) {
            if (!\Live\Lib\ConnUserStorage::getInstance()->get($fd))
                $this->sw->close($fd);
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
            $this->response($frame->fd, self::$msg);
        }
    }
}

\Swoolet\Router::$delimiter = '_';

Server::createServer('Live', $env)->run(':9502');