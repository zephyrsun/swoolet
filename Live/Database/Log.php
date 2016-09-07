<?php
/**
 * Created by IntelliJ IDEA.
 * User: sunzhenghua
 * Date: 15/6/21
 * Time: 下午8:46
 */

namespace Live\Database;

use Swoolet\Data\PDO;

class Log extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'log';

    public function __construct()
    {
        $this->option['dbname'] = 'live';

        parent::__construct();

        $this->cache = new \Live\Redis\Room();
    }

    public function hashTable($key)
    {
        PDO::hashTable($this->table_prefix);

        return $this;
    }

    public function add($request, $data)
    {
        if (is_array($data))
            $data = json_encode($data, \JSON_UNESCAPED_UNICODE);

        //if (!$code)
        //    $code = date('Ymd', \APP_TS);

        return $this->hashTable('log')->insert([
            'get' => isset($request->get) ? \http_build_query($request->get) : '', //get
            'post' => isset($request->post) ? http_build_query($request->post) : '', //post
            'data' => $data,
            'srv_ip' => \current(\swoole_get_local_ip()),
            'client_ip' => $request->server['remote_addr'],
            'time' => \date("Y-m-d H:i:s", \Swoolet\App::$ts), //time
        ]);
    }
}