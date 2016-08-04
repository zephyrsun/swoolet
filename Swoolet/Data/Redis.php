<?php

namespace Swoolet\Data {

    use \Swoolet\App;

    class Redis
    {
        static public $ins = [];

        public $option = ['host' => '127.0.0.1', 'port' => 6379, 'timeout' => 0.0, 'password' => ''];

        /**
         * @var \redisProxy
         */
        public $link;

        /*
         * need setup
         */
        public $cfg_key = '';
        public $db_index = 0;

        public function __construct()
        {
            if ($this->cfg_key)
                $this->dial($this->cfg_key);
        }

        /**
         * @param $cfg_key
         * @return \redisProxy
         */
        public function dial($cfg_key)
        {
            if ($link = &self::$ins[$cfg_key])
                return $this->link = $link;

            $cfg = App::getConfig($cfg_key) + $this->option;

            //https://github.com/swoole/php-cp
            $link = new \redisProxy();

            $link->connect($cfg['host'], $cfg['port'], $cfg['timeout']);

            if ($cfg['password'])
                $link->auth($cfg['password']);

            if (!$link->select($this->db_index))
                return null;

            return $this->link = $link;
        }

        public function __destruct()
        {
            if ($this->link)
                $this->link->release();
        }
    }
}


namespace {

    if (!class_exists('\redisProxy', false)) {
        class redisProxy extends \Redis
        {
            public function release()
            {
            }
        }
    }
}