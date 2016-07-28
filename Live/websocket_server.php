<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

class WebSocket extends \Swoolet\WebSocket
{
    /**
     * @var \Live\Lib\Conn
     */
    static public $conn;

    public function onClose($sw, $fd, $from_id)
    {
        //echo 'onClose' . PHP_EOL;
        self::$conn->quitConn($fd);
    }

    /**
     * ['m' => 'login', 'uid' => 1, 'token' => 'xxxxxx']
     *
     * @param swoole_websocket_frame $frame
     */
    public function parseData($frame)
    {
        if (!$frame->data)
            return;

        $_GET = \json_decode($frame->data, true);

        if (is_array($_GET)) {
            $uri = array_shift($_GET);

            \Swoolet\App::callRequest($uri, $frame);
        } else {
            $this->response('');
        }
    }
}

\Swoolet\Router::$delimiter = '_';

$app = WebSocket::createServer('Live', 'dev');
$app->run(':9502', [
    'open_tcp_keepalive' => 1,
    'tcp_keepidle' => 60,
    'tcp_keepinterval' => 60,
    'tcp_keepcount' => 5,
]);