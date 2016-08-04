<?php

define('TEST', true);

$db_ip = '127.0.0.1';
$db_port = 3366;
$db_user = 'root';
$db_pass = 'ZAXSq1w2';

return [
    'crypt' => ['key' => 'BKeVxo9IKu+k', 'secret' => 'KoP9FIPy+SVaE4F'],
    'redis_1' => ['host' => '127.0.0.1', 'port' => 6379, 'timeout' => 0.0, 'password' => ''],
    'db_1' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_user, 'password' => $db_pass],
    'db_2' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_user, 'password' => $db_pass],
];