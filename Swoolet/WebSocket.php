<?php

namespace Swoolet;

class WebSocket extends Basic
{
    /**
     * @var \swoole_websocket_server
     */
    public $sw;

    protected function runServer($host, $port)
    {
        //$this->events[] = 'HandShake';
        $this->events[] = 'Request';
        $this->events[] = 'Open';
        $this->events[] = 'Message';

        $q = include BASE_DIR . "Example/Config/dev.php";
        //var_dump($q);

        return new \swoole_websocket_server($host, $port, $this->mode, $this->sock_type);
    }

    /**
     * visit by HTTP
     *
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     *
     * @return null
     */
    public function onRequest($request, $response)
    {
        //parent::response('404');
        $this->sw->close($this->fd);
    }

    public function onHandShake($request, $response)
    {
        //echo 'onHandShake' . PHP_EOL;
    }

    public function onOpen($sw, $request)
    {
        //\ob_start();
        //Router::callRequest($request);
        //$this->response(\ob_get_clean());
        //echo 'onOpen' . PHP_EOL;
    }

    /**
     * @param $sw
     * @param $frame \swoole_websocket_frame
     */
    public function onMessage($sw, $frame)
    {
        if (!$frame->finish) {
            return;
        }

        \ob_start();
        /*
        if ($frame->opcode == \WEBSOCKET_OPCODE_BINARY) {
        } else {
        }
        */
        $this->parseData($frame);

        $this->response(\ob_get_clean());
    }

    /**
     * @param \swoole_websocket_frame $frame
     */
    public function parseData($frame)
    {
        $this->response("Receiced: {$frame->data}");
    }

    public function response($str)
    {
        //$str = \gzdeflate($str, 1);
        //$this->sw->push($this->fd, \implode("\r\n", $header) . "\r\n\r\n");

        if ($str)
            $this->sw->push($this->fd, $str);
    }
}