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

    public $option = ['host' => '127.0.0.1', 'port' => 6379, 'password' => '', 'debug' => false];

    public $link;

    public $cfg_key = '';
    public $db_index = 0;

    public $cache_key = '';

    public function __construct($cfg_key = '')
    {
        if ($cfg_key || $cfg_key = $this->cfg_key) {
            $this->option = App::getConfig($cfg_key) + $this->option;
        }
    }

    public function hmset($key, array $value, $callback)
    {
        $line = ['hmset', $key];
        foreach ($value as $k => $v) {
            $line[] = $k;
            $line[] = $v;
        }
        $link = $this->connect();
        $link->command($line, $callback);
    }

    public function hmget($key, array $value, $callback)
    {
        $link = $this->connect();
        $link->fields = $value;

        array_unshift($value, 'hmget', $key);
        $link->command($value, $callback);
    }

    public function subscribe($key, $callback)
    {
        $link = $this->connect();
        $link->command(['subscribe', $key], $callback);
    }

    public function unsubscribe($key, $callback)
    {
        $link = $this->connect();
        $link->command(['unsubscribe', $key], $callback);
    }

    public function __call($method, array $args)
    {
        $callback = array_pop($args);
        array_unshift($args, $method);
        $link = $this->connect();
        $link->command($args, $callback);
    }

    /**
     * 从连接池中取出一个连接资源
     * @return RedisConnection
     */
    protected function connect()
    {
        if ($ins = &self::$ins[$this->cache_key])
            return $ins;

        $cfg = $this->option;

        $link = new RedisConnection();
        $link->connect($cfg['host'], $cfg['port'], $this->cache_key);
        $link->debug = $cfg['debug'];

        if ($cfg['password']) {
            $link->command(['auth', $cfg['password']], function () {
                //var_dump(func_get_args());
            });
        }

        $link->command(['select', $this->db_index], function () {
            //var_dump(func_get_args());
        });

        return $ins = $link;
    }

    /**
     * @param $key
     * @return \Swoolet\Data\RedisConnection
     */
    static public function &getConnection($key)
    {
        return self::$ins[$key];
    }

    /**
     * @param $key
     */
    static public function release($key)
    {
        if ($ins = self::getConnection($key)) {
            $ins->command(['close'], function () {
            });

            unset(self::$ins[$key]);
        }
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
    protected $cb = '';
    protected $cb_pool = [];

    /**
     * 等待发送的数据
     */
    protected $wait_send = '';
    protected $wait_recv = false;
    public $fields;

    public $debug = false;

    public function __construct()
    {
    }

    public function connect($host, $port, $key)
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $client->on('connect', [$this, 'onConnect']);
        $client->on('error', [$this, 'onError']);
        $client->on('receive', [$this, 'onReceive']);
        $client->on('close', [$this, 'onClose']);
        $client->connect($host, $port);

        $client->on('connect', [$this, 'onConnect']);
        $client->on('error', [$this, 'onError']);
        $client->on('receive', [$this, 'onReceive']);
        $client->on('close', [$this, 'onClose']);

        $this->client = $client;

        return $this;
    }

    /**
     * 清理数据
     */
    public function clean()
    {
        $this->buffer = '';
        // $this->cb = '';
        $this->wait_send = '';
        $this->wait_recv = false;
        $this->fields = [];
    }

    /**
     * 执行redis指令
     * @param $cmd
     * @param $cb
     */
    public function command($cmd, $cb)
    {
        $cmd = $this->parseRequest($cmd);
        if ($this->client->isConnected()) {
            //如果已经连接，直接发送数据
            $this->client->send($cmd);
        } else {
            //未连接，等待连接成功后发送数据
            $this->wait_send .= $cmd;
        }

        $this->cb_pool[] = $cb;
    }

    public function onConnect(\swoole_client $client)
    {
        $client->send($this->wait_send);

        $this->wait_send = '';
    }

    public function onError()
    {
        echo 'Failed to connect redis server' . PHP_EOL;
    }

    public function onClose(\swoole_client $sw)
    {
        $cb = $this->getCallback();
        $cb('timeout', false);
    }

    public function getCallback()
    {
        if (count($this->cb_pool) > 0)
            $this->cb = array_shift($this->cb_pool);

        return $this->cb;
    }

    public function onReceive($sw, $data)
    {
        if ($this->debug)
            $this->trace($data);

        if ($this->wait_recv) {

            $this->buffer .= $data;

            if (strlen($this->buffer) >= $this->wait_recv) {
                $result = substr($this->buffer, 0, -2);

                $cb = $this->getCallback();
                $cb($result);
            } else
                return;

        } else {
            $this->buffer = $data;

            while ($this->buffer) {
                $result = $this->read();
                if ($this->wait_recv)
                    return;

                $cb = $this->getCallback();
                $cb($result);
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
                if ($payload == -1)
                    return null;

                $len = $payload;
                $chunk = $this->readBucket($len);
                if ($len > strlen($chunk)) {
                    $this->wait_recv = $len + 2;
                    $this->buffer = $chunk;
                    return null;
                }

                return $chunk;

            case '*':
                if ($payload == -1)
                    return null;

                $bulk = [];
                for ($i = 0; $i < $payload; ++$i)
                    $bulk[$i] = $this->read();

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
        echo '-----------------------------------------' . PHP_EOL;
        echo trim($msg) . PHP_EOL;
        echo '-----------------------------------------' . PHP_EOL;
    }

}
