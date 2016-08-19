<?php
namespace Swoolet\Data;

use \Swoolet\App;

/**
 * Class Redis
 *
 * based on https://github.com/swoole/redis-async
 *
 * @method set
 * @method get
 * @method select
 * @method hexists
 * @method sadd
 * @method sMembers
 */
class RedisAsync
{
    static public $ins;

    public $option = ['host' => '127.0.0.1', 'port' => 6379, 'password' => ''];

    public $link;

    public $cfg_key = '';
    public $db_index = 0;

    public $cache_key = '';

    public $debug = false;

    public function __construct($cfg_key = '', $cache_key = '')
    {
        if ($cfg_key || $cfg_key = $this->cfg_key) {
            $this->option = App::getConfig($cfg_key) + $this->option;
            $this->cache_key = $cache_key ? $cache_key : $cfg_key;
        }
    }

    public function hmset($key, array $value, $callback)
    {
        $lines = ['hmset', $key];
        foreach ($value as $k => $v) {
            $lines[] = $k;
            $lines[] = $v;
        }
        $conn = $this->getConnection();
        $cmd = $conn->parseRequest($lines);
        $conn->command($cmd, $callback);
    }

    public function hmget($key, array $value, $callback)
    {
        $conn = $this->getConnection();
        $conn->fields = $value;

        array_unshift($value, 'hmget', $key);
        $cmd = $conn->parseRequest($value);
        $conn->command($cmd, $callback);
    }

    public function __call($method, array $args)
    {
        $callback = array_pop($args);
        array_unshift($args, $method);
        $conn = $this->getConnection();
        $cmd = $conn->parseRequest($args);
        $conn->command($cmd, $callback);
    }

    /**
     * 从连接池中取出一个连接资源
     * @return RedisConnection
     */
    protected function getConnection()
    {
        if ($ins = &self::$ins[$this->cache_key])
            return $ins;

        $cfg = $this->option;

        $link = new RedisConnection();
        $link->connect($cfg['host'], $cfg['port']);

        if ($this->option['password']) {
            $link->command('auth', $cfg['password'], function () {
            });
        }

        $link->command('select', $this->db_index, function () {
        });

        return $ins = $link;
    }

    static public function &getInstance($key)
    {
        return self::$ins[$key];
    }
}

class RedisConnection
{
    public $crlf = "\r\n";

    protected $buffer = '';
    /**
     * @var \swoole_client
     */
    protected $client;
    protected $callback;

    /**
     * 等待发送的数据
     */
    protected $wait_send = false;
    protected $wait_recv = false;
    public $fields;

    public $debug = false;

    public function __construct()
    {
    }

    public function connect($host, $port)
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $client->on('connect', [$this, 'onConnect']);
        $client->on('error', [$this, 'onError']);
        $client->on('receive', [$this, 'onReceive']);
        $client->on('close', [$this, 'onClose']);
        $client->connect($host, $port);

        $this->client = $client;

        return $this;
    }

    /**
     * 清理数据
     */
    public function clean()
    {
        $this->buffer = '';
        $this->callback;
        $this->wait_send = false;
        $this->wait_recv = false;
        $this->fields = array();
    }

    /**
     * 执行redis指令
     * @param $cmd
     * @param $callback
     */
    public function command($cmd, $callback)
    {
        /**
         * 如果已经连接，直接发送数据
         */
        if ($this->client->isConnected()) {
            $this->client->send($cmd);
        } /**
         * 未连接，等待连接成功后发送数据
         */
        else {
            $this->wait_send = $cmd;
        }
        $this->callback = $callback;
        //从空闲连接池中移除，避免被其他任务使用
    }

    public function onConnect(\swoole_client $client)
    {
        if ($this->wait_send) {
            $client->send($this->wait_send);
            $this->wait_send = '';
        }
    }

    public function onError()
    {
        echo 'Failed to connect redis server' . PHP_EOL;
    }

    public function onClose(\swoole_client $cli)
    {
        if ($this->wait_send)
            call_user_func($this->callback, 'timeout', false);
    }

    public function onReceive($cli, $data)
    {
        if ($this->debug)
            $this->trace($data);

        $this->trace($data);

        $result = null;
        if ($this->wait_recv) {

            $this->buffer .= $data;

            if (strlen($this->buffer) >= $this->wait_recv) {
                $result = substr($this->buffer, 0, -2);

                call_user_func($this->callback, $result, $result === null);
            } else
                return;

        } else {
            $this->buffer = $data;

            while ($this->buffer) {
                $result = $this->read();
                if ($this->wait_recv)
                    return;

                call_user_func($this->callback, $result, $result === null);
            }
        }

        $this->clean();
    }

    public function read()
    {
        $chunk = $this->readLine();

        $prefix = $chunk[0];
        $payload = substr($chunk, 1);

        switch ($prefix) {
            case '+':
                return $payload;

            case '$':
                if ($payload == -1) {
                    return null;
                }

                $len = $payload;
                $chunk = $this->readBucket($len);
                if ($len > strlen($chunk)) {
                    $this->wait_recv = $len + 2;
                    $this->buffer = $chunk;
                    return null;
                }
                return $chunk;

            case '*':
                if ($payload == -1) {
                    return null;
                }

                $bulk = array();
                for ($i = 0; $i < $payload; ++$i) {
                    $bulk[$i] = $this->read();
                }

                return $bulk;

            case ':':
                return (int)$payload;

            case '-':
                return $payload;

            default:
                echo "Response is not a redis result. String:\n$payload\n";
                return null;
        }
    }

    public function readLine()
    {
        list($chunk, $this->buffer) = \explode($this->crlf, $this->buffer, 2);
        return $chunk;
    }

    public function readBucket($len)
    {
        $chunk = substr($this->buffer, 0, $len);
        if ($this->buffer)
            $this->buffer = substr($this->buffer, $len + 2);

        return $chunk;
    }

    public function parseRequest($arr)
    {
        $cmd = '*' . count($arr) . $this->crlf;
        foreach ($arr as $item)
            $cmd .= '$' . strlen($item) . $this->crlf . $item . $this->crlf;

        return $cmd;
    }

    public function trace($msg)
    {
        echo "-----------------------------------------" . PHP_EOL;
        echo trim($msg) . PHP_EOL;
        echo "-----------------------------------------" . PHP_EOL;
    }

}
