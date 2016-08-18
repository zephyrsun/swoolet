<?php

namespace Swoolet;

use Swoolet\Data\PDO;
use Swoolet\Data\Redis;

class Socket
{
    public $events = [
        'Start',
        'Shutdown',
        'WorkerStart',
        'WorkerStop',
        'Connect',
        'Receive',
        'Packet',
        'Close',
        'Task',
        'Finish',
        //'Timer',
    ];

    //https://github.com/wanhuo/swoole-doc-stu/blob/master/doc/01.swoole_server%E9%85%8D%E7%BD%AE%E9%80%89%E9%A1%B9.md
    public $option = [

        //'worker_num' => 8,
        //'reactor_num' => 2,

        'max_request' => 1000,
        'max_conn' => 1000,
        'ipc_mode' => 1,
        'debug_mode' => 1,
        'dispatch_mode' => 2,
        'daemonize' => 0,
        'log_file' => '',
        'open_cpu_affinity' => 1,
        'backlog' => 128,

        'task_worker_num' => 2,
        'task_max_request' => 1000,
        'task_ipc_mode' => 2,
        'task_tmpdir' => '/tmp/task/',

        'open_eof_check' => true,
        'package_eof ' => "\r\n",

        //'open_length_check' => true,
        //'package_length_offset' => 5,
        //'package_body_offset' => 10,
        //'package_length_type' => 'N',
        //'package_max_length' => 8192,

        'open_tcp_nodelay' => 1,
        'tcp_defer_accept' => 1,

        //'ssl_cert_file' => '/path/to/ssl.crt',
        //'ssl_key_file' => '/path/to/ssl.key',

        //'open_tcp_keepalive' => 1,
        //'tcp_keepidle' => 60,
        //'tcp_keepinterval' => 60,
        //'tcp_keepcount' => 5,

        //'heartbeat_check_interval' => 60,
        //'heartbeat_idle_time' => 600,
    ];

    public $mode = \SWOOLE_PROCESS;
    public $sock_type = \SWOOLE_SOCK_TCP;

    /**
     * @var \swoole_server
     */
    public $sw;
    public $fd;

    public $namespace = 'App';
    public $env = 'test';

    public function __construct($namespace, $env)
    {
        $this->namespace = $namespace;
        $this->env = $env;

        App::setConfig($namespace, $env);
        App::$ts = \time();
    }

    /**
     * a shortcut
     *
     * @param $namespace
     * @return Socket
     */
    static function createServer($namespace, $env)
    {
        $class = get_called_class();

        return App::$server = new $class($namespace, $env);
    }

    /**
     * @param $mode
     *        - SWOOLE_BASE
     *        - SWOOLE_PROCESS
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @param $sock_type
     *          - SWOOLE_SOCK_UDP
     *          - SWOOLE_SOCK_TCP
     *          - SWOOLE_SOCK_TCP | SWOOLE_SSL (use ssl)
     */
    public function setSockType($sock_type)
    {
        $this->sock_type = $sock_type;
    }

    /**
     * @param $address
     *        - :9501
     *        - 127.0.0.1:9501
     * @return \swoole_server
     */
    public function run($address)
    {
        register_shutdown_function([$this, 'fatalHandler']);

        list($host, $port) = explode(':', $address);
        if (!$host)
            $host = '0.0.0.0';

        $sw = $this->runServer($host, $port);

        $setting = App::getConfig('swoole') + $this->option;
        if (!$setting['log_file'])
            $setting['log_file'] = "/tmp/swoole_{$port}.log";

        $sw->set($setting);

        foreach ($this->events as $event)
            $sw->on($event, [$this, 'on' . $event]);

        $this->sw = $sw;

        $sw->start();
    }

    protected function runServer($host, $port)
    {
        return new \swoole_server($host, $port, $this->mode, $this->sock_type);
    }

    public function onStart($sw)
    {
        //echo 'onStart' . PHP_EOL;
    }

    public function onShutdown($sw)
    {
        //echo 'onShutdown' . PHP_EOL;
    }

    public function onWorkerStart($sw, $worker_id)
    {
        echo 'onWorkerStart' . PHP_EOL;

        function_exists('opcache_reset') && opcache_reset();
        function_exists('apc_clear_cache') && apc_clear_cache();

        App::setConfig($this->namespace, $this->env);

        PDO::$ins = [];
        Redis::$ins = [];

        //var_dump(APP::$config);
    }

    public function onWorkerStop($sw, $worker_id)
    {
        echo 'onWorkerStop' . PHP_EOL;
    }

    public function onConnect($sw, $fd, $from_id)
    {
        //echo 'onConnect' . PHP_EOL;
    }

    public function onReceive($sw, $fd, $from_id, $data)
    {
        //echo 'onReceive' . PHP_EOL;
    }

    public function onPacket($sw, $data, $client_info)
    {
        //echo 'onPacket' . PHP_EOL;
    }

    public function onClose($sw, $fd, $from_id)
    {
        //echo 'onClose' . PHP_EOL;
    }

    public function onTask($sw, $task_id, $from_id, $data)
    {
        //echo 'onTask' . PHP_EOL;
        return true;
    }

    public function onFinish($sw, $task_id, $data)
    {
        //echo 'onFinish' . PHP_EOL;
    }

    public function callRequest($uri, $request)
    {
        App::$ts = \time();

        $query = Router::parse($uri);

        $class = $this->namespace . '\\Controller\\' . \ucfirst($query[0]);
        if (class_exists($class)) {
            $obj = App::getInstance($class);
            return $obj->{$query[1]}($request);
        }
    }

    /**
     * @param $fd
     * @param $str
     */
    public function response($fd, $str)
    {
        if ($str)
            $this->sw->send($fd, $str);
    }

    public function fatalHandler()
    {
        print_r(\ob_get_clean());
        //$this->response(\ob_get_clean());
    }
}