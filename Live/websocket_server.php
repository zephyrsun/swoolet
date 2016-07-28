<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

class WebSocket extends \Swoolet\WebSocket
{
    public function onRequest($request, $response)
    {
        \Swoolet\Basic::response('Please visit: http://www.camhow.cn/');
        parent::onRequest($request, $response);
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

        $uri = array_shift($_GET);

        \Swoolet\App::callRequest($uri, $frame);
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