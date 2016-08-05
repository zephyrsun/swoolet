<?php

if (!$env = &$argv[1]) {
    echo 'Please input ENV' . PHP_EOL;
    return;
}

error_reporting(E_ALL);

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
        $this->response = $response;

        if ($request->server['path_info'] == '/favicon.ico')
            return $this->response('');

        if (isset($request->post))
            $_POST = $request->post;

        $this->callRequest($request->server['path_info'], $request);

        $this->response(self::$msg);
    }
}

$app = Server::createServer('Live', $env);
$app->run(':80');