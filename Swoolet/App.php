<?php

/**
 * Swoolet
 *
 * @package   Swoolet
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 */

namespace Swoolet;

define('BASE_DIR', dirname(__DIR__) . \DIRECTORY_SEPARATOR);

\spl_autoload_register('\Swoolet\import');

class App
{
    /**
     * @var Socket $server
     */
    static public $server;
    static public $ins;
    static public $config;
    static public $ts = 0;

//    static public function response($str)
//    {
//        self::$server->response($str);
//    }

    static public function setConfig($namespace, $env)
    {
        return self::$config = import($namespace . '/Config/' . $env) or self::$config = [];
    }

    static public function getConfig($key)
    {
        return isset(self::$config[$key]) ? self::$config[$key] : [];
    }

    static public function getInstance($class, array $args = [], $key = '')
    {
        $key or $key = $class;

        if ($obj = &self::$ins[$key])
            return $obj;

        $n = count($args);
        if ($n == 0)
            return $obj = new $class();
        elseif ($n == 1)
            return $obj = new $class($args[0]);
        elseif ($n == 2)
            return $obj = new $class($args[0], $args[1]);
        else
            return $obj = new $class($args[0], $args[1], $args[2]);
    }
}

class Controller
{
    public $request;

    public function __call($name, $arguments)
    {
        $class = get_called_class();
        echo "Method error: {$class}::{$name}" . PHP_EOL;
    }
}

class Router
{
    static public $delimiter = '/';

    /**
     * @param  string $uri
     * @return array
     */
    static public function parse($uri)
    {
        return ($uri == '/' ? [] : \explode(self::$delimiter, \trim($uri, '/'))) + ['Index', 'index'];
    }
}

abstract class Result implements \Iterator, \ArrayAccess, \Countable
{
    private $__data = [];

    /**
     * @param $key
     * @param $val
     *
     * @return Result
     */
    public function __set($key, $val)
    {
        $this->__data[$key] = $val;

        return $this;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->__data[$key];
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->__data[$key]);
    }

    /**
     * @param $key
     *
     * @return Result
     */
    public function __unset($key)
    {
        unset($this->__data[$key]);

        return $this;
    }

    /**
     * @param       $key
     * @param mixed $val
     *
     * @return Array
     */
    public function set($key, $val = null)
    {
        if (\is_array($key))
            $this->__data = $key + $this->__data;
        elseif ($key)
            $this->__set($key, $val);

        return $this->__data;
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    public function get($key = null)
    {
        if ($key === null)
            return $this->__data;

        return $this->__get($key);
    }

    /**
     * @param $key
     *
     * @return Result
     */
    public function delete($key)
    {
        if (\is_array($key)) {
            foreach ($key as $k => $v)
                $this->__unset($k);
        } else
            $this->__unset($key);

        return $this;
    }

    /**
     * @return Result
     */
    public function flush()
    {
        $this->__data = [];

        return $this;
    }

    // Iterator Methods

    /**
     * @return mixed
     */
    public function rewind()
    {
        return \reset($this->__data);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return \current($this->__data);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return \key($this->__data);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return \next($this->__data);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->key() !== null;
    }

    // Countable Methods

    /**
     * @return int
     */
    public function count()
    {
        return \count($this->__data);
    }

    // ArrayAccess Methods

    /**
     * @param $key
     * @param $val
     *
     * @return Result
     */
    public function offsetSet($key, $val)
    {
        return $this->__set($key, $val);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->__get($key);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->__isset($key);
    }

    /**
     * @param $key
     *
     * @return Result
     */
    public function offsetUnset($key)
    {
        return $this->__unset($key);
    }
}

/**
 * @param $name
 * @return mixed
 */
function import($name)
{
    $name = BASE_DIR . \str_replace('\\', \DIRECTORY_SEPARATOR, $name) . '.php';
    if (\is_file($name))
        return include $name;

    return null;
}

function log($msg, $id)
{
    echo date("Y-m-d H:i:s", \Swoolet\App::$ts) . " ($id)" . $msg . PHP_EOL;
}