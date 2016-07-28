<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

$app = \Swoolet\Http::createServer('Example', 'dev');
$app->run(':9501');