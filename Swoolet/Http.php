<?php

namespace Swoolet;

class Http extends Basic
{
    public $content_type = 'text/html; charset=utf-8';

    protected function runServer($host, $port)
    {
        $this->events[] = 'Request';

        return new \swoole_http_server($host, $port, $this->mode, $this->sock_type);
    }

    /**
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     *
     * @return null
     */
    public function onRequest($request, $response)
    {
        if ($request->server['path_info'] == '/favicon.ico')
            return $this->response('');

        $this->parseData($request);
    }

    /**
     * @param \swoole_http_request $request
     */
    public function parseData($request)
    {
        App::callRequest($request->server['path_info']);
    }

    /**
     * @param $str
     * @return null
     */
    public function response($str)
    {
        //$str = \gzdeflate($str, 1);

        $header = array(
            'HTTP/1.1 200 OK',
            'Date' => \gmdate('D, d M Y H:i:s T', \APP_TS),
            'Content-Type' => $this->content_type,
            'Content-Length' => \strlen($str),
            'Content-Encoding' => 'deflate',
            //'KeepAlive: off',
            //'Connection: close',
        );

        $this->sw->send($this->fd, \implode("\r\n", $header) . "\r\n\r\n");

        if ($str)
            $this->sw->send($this->fd, $str);

        $this->sw->close($this->fd);
    }
}