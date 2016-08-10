<?php

error_reporting(E_ALL);

$db_ip = '127.0.0.1';
$db_port = 3366;
$db_user = 'root';
$db_pass = 'ZAXSq1w2';

return [
    'swoole' => [
        'worker_num' => 4,
        'reactor_num' => 4,

        'open_tcp_keepalive' => 1,
        'tcp_keepidle' => 60,
        'tcp_keepinterval' => 60,
        'tcp_keepcount' => 5,
        'daemonize' => 0,
        'heartbeat_check_interval' => 60,
        'heartbeat_idle_time' => 600,
    ],
    'crypt' => ['key' => 'BKeVxo9IKu+k', 'secret' => 'KoP9FIPy+SVaE4F'],
    'redis_1' => ['host' => '127.0.0.1', 'port' => 6379, 'timeout' => 0.0, 'password' => ''],
    'db_1' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_user, 'password' => $db_pass],
    'db_2' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_user, 'password' => $db_pass],
];