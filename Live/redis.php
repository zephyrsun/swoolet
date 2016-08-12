<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

\Swoolet\App::setConfig('Live', 'dev');

$redis = new \Swoolet\Data\RedisAsync();

$redis->debug = 1;
$data = str_repeat('1234567890', 50000);


//$redis->set('long', $data, function ($result, $success) {
//    var_dump('set', $result, $success);
//});
//
//$redis->get('long', function ($result, $success) {
//    var_dump('get', strlen($result), $success);
//});


//for ($i = 0; $i < 1; $i++) {
//    $redis->subscribe('test', function ($result, $success) {
//        var_dump('subscribe', $result, $success);
//    });
//}

$redis->publish('test', '111', function ($result, $success) {
    var_dump('publish', $result, $success);
});