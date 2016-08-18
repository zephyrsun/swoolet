<?php

if (!$env = &$argv[1]) {
    echo 'Please input ENV' . PHP_EOL;
    return;
}

include \dirname(__DIR__) . '/Swoolet/App.php';

use \Live\Lib\Conn;

class Server extends \Swoolet\WebSocket
{
    static public $msg;
    /**
     * @var \Live\Lib\Conn
     */
    static public $conn;

    public function onWorkerStart($sw, $worker_id)
    {
        parent::onWorkerStart($sw, $worker_id);

        Server::$conn = new \Live\Lib\Conn();
        Server::$conn->onWorkerStart($sw, $worker_id);
    }

    public function onWorkerStop($sw, $worker_id)
    {
        parent::onWorkerStop($sw, $worker_id);

        Server::$conn->onWorkerStop($sw, $worker_id);
    }

    public function onOpen($sw, $request)
    {
        $fd = $request->fd;

        //没有成功登陆,踢出去
        swoole_timer_after(1500, function () use ($fd) {
            if (self::$conn && !self::$conn->getConn($fd)) {
                //Conn::$ins->quitConn($fd);
                $this->sw->close($fd);
            }
        });
    }

    public function onClose($sw, $fd, $from_id)
    {
        \Swoolet\log('onClose', $fd);
        if (Server::$conn) {
            self::$conn->leave($fd);
        }
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

        $_POST = \json_decode($frame->data, true);
        if ($_POST && $uri = &$_POST['m']) {

            $this->callRequest($uri, $frame);
            \Swoolet\log($uri, $frame->fd);

            $this->response($frame->fd, self::$msg);
        }
    }
}

\Swoolet\Router::$delimiter = '_';

$app = Server::createServer('Live', $env);
$app->run(':9502');