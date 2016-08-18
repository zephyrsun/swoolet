<?php

namespace Swoolet;

class WebSocket extends Socket
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
    }

    public function onHandShake($request, $response)
    {
        // echo 'onHandShake' . PHP_EOL;
    }

    /**
     * @param \swoole_websocket_server $sw
     * @param \swoole_http_request $request
     */
    public function onOpen($sw, $request)
    {
        //\ob_start();
        //Router::callRequest($request);
        //$this->response(\ob_get_clean());
        //echo 'onOpen' . PHP_EOL;
    }

    /**
     * @param \swoole_websocket_server $sw
     * @param \swoole_websocket_frame $frame
     */
    public function onMessage($sw, $frame)
    {
        if (!$frame->finish) {
            return;
        }

        /*
        if ($frame->opcode == \WEBSOCKET_OPCODE_BINARY) {
        } else {
        }
        */
        $this->response($frame->fd, "Receiced: {$frame->data}");
    }

    public function response($fd, $str)
    {
        //$str = \gzdeflate($str, 1);
        //$this->sw->push($this->fd, \implode("\r\n", $header) . "\r\n\r\n");

        $this->sw->push($fd, $str);
    }
}