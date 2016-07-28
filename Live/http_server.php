<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

$app = \Swoolet\Http::createServer('Live', 'dev');
$app->run(':80');