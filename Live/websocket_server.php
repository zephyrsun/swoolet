<?php

if (!$env = &$argv[1]) {
    echo 'Please input ENV' . PHP_EOL;
    return;
}

error_reporting(E_ALL);

include \dirname(__DIR__) . '/Swoolet/App.php';

use \Live\Lib\Conn;

class Server extends \Swoolet\WebSocket
{
    static public $msg;

    public function onOpen($sw, $request)
    {
        $fd = $request->fd;
        //没有成功登陆,踢出去
        swoole_timer_after(1500, function () use ($fd) {
            if (!Conn::$ins->getConn($fd))
                $this->sw->close($fd);
        });
    }

    public function onClose($sw, $fd, $from_id)
    {
        //echo 'onClose' . PHP_EOL;
        if (Conn::$ins)
            Conn::$ins->quitConn($fd);
    }

    /**
     * ['m' => 'login', 'uid' => 1, 'token' => 'xxxxxx']
     *
     * @param swoole_websocket_frame $frame
     */
    public function onMessage($sw, $frame)
    {
        if (!$frame->data)
            return;

        $_POST = \json_decode($frame->data, true);

        if (is_array($_POST)) {
            //$uri = array_shift($_POST);
            $uri = \current($_POST);
            echo $uri . PHP_EOL;

            $this->callRequest($uri, $frame);

            $this->response(self::$msg);
        } else {
            $this->response('');
        }
    }
}

\Swoolet\Router::$delimiter = '_';

$app = Server::createServer('Live', $env);
$app->run(':9502');