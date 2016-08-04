<?php

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

$app = Server::createServer('Live', 'dev');
$setting = include './Config/swoole_setting.php';
$app->run(':80', $setting);