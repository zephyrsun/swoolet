<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

$app = \Swoolet\Socket::createServer('Live', 'dev');
new \Live\Controller\Basic();

$str = substr('15921258787', -4);
var_dump($str);
//$data = (new \Live\Lib\Live())->stop(1);
//var_dump($data);


//$link = new \redisProxy();
//$link->connect('127.0.0.1', 6366, 0);
//
//$link->select(0);

//  $db_user = new \Live\Database\UserLevel();
//var_export(\Live\Database\UserLevel::q());
//$user = $db_user->add(1, 1);

//var_dump($user);


/*
class  test
{
    public $link;
    public $channel = 'test';

    public function __construct()
    {
        $this->link = new \Redis();
        $this->link->connect('127.0.0.1', '6379');
    }

    function sub()
    {
        $this->link->subscribe([$this->channel], function ($redis, $chan, $msg) {
            var_dump('1111', $chan, $msg);
        });
    }

    public function __destruct()
    {
        var_dump('dest');
    }
}

$test = new test();
$test->sub();
sleep(1e5);
*/

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