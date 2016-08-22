<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

\Swoolet\App::setConfig('Live', 'dev');

$redis->debug = 1;
$data = str_repeat('1234567890', 50000);


//$redis->set('long', $data, function ($result, $success) {
//    var_dump('set', $result, $success);
//});
//
//$redis->get('long', function ($result, $success) {
//    var_dump('get', strlen($result), $success);
//});


for ($i = 0; $i < 3; $i++) {

    $redis = new \Swoolet\Data\RedisAsync('redis_async', $i);
    $redis->subscribe('test' . $i, function ($result, $err) use ($i) {
        var_dump('subscribe:' . $i, $result, $err);
    });
}

$redis->publish('test1', '111', function ($result, $err) {
    var_dump('publish', $result, $err);
});