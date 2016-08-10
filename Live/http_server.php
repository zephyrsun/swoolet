<?php

if (!$env = &$argv[1]) {
    echo 'Please input ENV' . PHP_EOL;
    return;
}

include \dirname(__DIR__) . '/Swoolet/App.php';

class Server extends \Swoolet\Http
{
    static public $msg;
    /**
     * @var \Live\Lib\Conn
     */
    static public $conn;

    public function onRequest($request, $response)
    {
        $this->resp = $response;

        if ($request->server['path_info'] == '/favicon.ico')
            return $this->response('');

        $_POST = isset($request->post) ? $request->post : array();

        $this->callRequest($request->server['path_info'], $request);

        $this->response(self::$msg);
    }
}

$app = Server::createServer('Live', $env);

Server::$conn = new \Live\Lib\Conn();

$app->run(':80');