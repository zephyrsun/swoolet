<?php

error_reporting(E_ALL);

$db_ip = '127.0.0.1';
$db_port = 3306;
$db_user = 'root';
$db_pass = 'sh.camhow.live@MySQL';
$db_option = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
];

$redis_pass = 'sh.camhow.live@Redis';

return [
    'swoole' => [
//        'worker_num' => 1,
//        'reactor_num' => 1,
        'dispatch_mode' => 5,

        'open_tcp_keepalive' => 1,
        'tcp_keepidle' => 60,
        'tcp_keepinterval' => 60,
        'tcp_keepcount' => 5,
        'daemonize' => 1,
        'heartbeat_check_interval' => 60,
        'heartbeat_idle_time' => 600,
    ],

    'qiniu' => [
        'access_key' => 'uk_JgveWYYcNXE730vQdHyRaAV86DplixzERLRy-',
        'secret_key' => 'EHNf0jpUcLa8iVRO47aL178lF_zcPnsEwTE4LD-c',
    ],

    'crypt' => ['key' => 'BKeVxo9IKu+k', 'secret' => 'KoP9FIPy+SVaE4F'],
    'redis_1' => ['host' => '127.0.0.1', 'port' => 6399, 'timeout' => 0.0, 'password' => $redis_pass],
    'redis_async' => ['host' => '127.0.0.1', 'port' => 6399, 'timeout' => 0.0, 'password' => $redis_pass],
    'db_1' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_user, 'password' => $db_pass, 'option' => $db_option],
    'db_2' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_user, 'password' => $db_pass, 'option' => $db_option],
];