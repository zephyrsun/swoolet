<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

class Server extends \Swoolet\WebSocket
{
    /**
     * @var \Live\Lib\Conn
     */
    static public $conn;

    public function onOpen($sw, $request)
    {
        $fd = $request->fd;
        //没有成功登陆,踢出去
        swoole_timer_after(1500, function () use ($fd) {
            if (!self::$conn->getConn($fd))
                $this->sw->close($fd);
        });
    }

    public function onClose($sw, $fd, $from_id)
    {
        //echo 'onClose' . PHP_EOL;
        if (self::$conn)
            self::$conn->quitConn($fd);
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
            $uri = array_shift($_POST);

            echo $uri . PHP_EOL;

            \Swoolet\App::callRequest($uri, $frame);
        } else {
            $this->response('');
        }
    }
}

\Swoolet\Router::$delimiter = '_';

$app = Server::createServer('Live', 'dev');
$setting = include './Config/swoole_setting.php';
$app->run(':9502', $setting);