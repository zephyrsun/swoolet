<?php

namespace Swoolet;

class Http extends Basic
{
    /**
     * @var \swoole_http_response $resp
     */
    public $resp;

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
        $this->resp = $response;

        if ($request->server['path_info'] == '/favicon.ico')
            return $this->response('');

        $this->response($this->callRequest($request->server['path_info'], $request));
    }

    /**
     * @param $str
     * @return null
     */
    public function response($str)
    {
        $this->resp->header("Server", "swoolet");
        $this->resp->end($str);
    }
}