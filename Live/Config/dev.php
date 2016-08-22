<?php

error_reporting(E_ALL);

$db_ip = '127.0.0.1';
$db_port = 3366;
$db_user = 'root';
$db_pass = 'ZAXSq1w2';

return [
    'swoole' => [
        'worker_num' => 2,
        'reactor_num' => 2,
        'dispatch_mode' => 2,
        'max_request' => 5,

        'open_tcp_keepalive' => 1,
        'tcp_keepidle' => 60,
        'tcp_keepinterval' => 60,
        'tcp_keepcount' => 5,
        'daemonize' => 0,
        'heartbeat_check_interval' => 60,
        'heartbeat_idle_time' => 600,
    ],

    'qiniu' => [
        'key' => 'uk_JgveWYYcNXE730vQdHyRaAV86DplixzERLRy-',
        'secret' => 'EHNf0jpUcLa8iVRO47aL178lF_zcPnsEwTE4LD-c',
        'hub' => 'kanhao',
    ],

    'jpush' => [
        'key' => '118a3ec296f6193665bdf95c',
        'secret' => 'f9c3c00704c1924d1ff62844',
    ],

    'crypt' => ['key' => 'BKeVxo9IKu+k', 'secret' => 'KoP9FIPy+SVaE4F'],
    'redis_1' => ['host' => '127.0.0.1', 'port' => 6366, 'timeout' => 0.0, 'password' => ''],
    'redis_async' => ['host' => '127.0.0.1', 'port' => 6366, 'timeout' => 0.0, 'password' => ''],
    'db_1' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_user, 'password' => $db_pass],
    'db_2' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_user, 'password' => $db_pass],
];