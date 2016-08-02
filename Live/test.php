<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

$crypt = new \Swoolet\Lib\Crypt('fadfaf');

$val = $crypt->encrypt(111);
var_dump($val);
//$crypt = new \Swoolet\Lib\Crypt('fadfaf','fadfa');
$q = $crypt->decrypt($val);
var_dump($q);

//$q = new \Live\Third\QCloud();

/*
$obj1 = new pdoProxy('mysql:host=127.0.0.1;port=3366;dbname=live_user;charset=utf8', "root", "ZAXSq1w2");
$rs = $obj1->prepare("select * from user_0");
var_dump($rs->execute(),$rs->fetch());
$obj1->release();
*/
/*
$rd = new \Live\Redis\User();
$ret = $rd->set('1', 1);
var_dump($ret);
*/
/*

$db = new \Live\Database\User();

$db->table('user_0');

$user = $db->getUser(1);

var_dump($user);
*/
/*
$arr = [
    "int" => 1,
    "float" => 0.5,
    "boolean" => true,
    "null" => null,
    "string" => "foo bar",
    "array" => [
        "foo",
        "bar"
    ],
    "object" => [
        "foo" => 1,
        "baz" => 0.5
    ]
];

$rd = new \Live\Redis\User();
$ret = $rd->set('1', $arr, 3600);
var_dump($ret);

$ret = $rd->get('1');
var_dump($arr);
*/


/*
$bin = \msgpack_pack($arr);
var_dump($bin, bin2hex($bin));

$arr = \msgpack_unpack($bin);
var_dump($arr);
*/