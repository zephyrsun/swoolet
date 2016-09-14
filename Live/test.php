<?php

include \dirname(__DIR__) . '/Swoolet/App.php';

\Swoolet\App::setConfig('live', 'dev');


error_reporting(E_ALL);

//
//$table = new swoole_table(2 ^ 32);
//$table->column('fd', swoole_table::TYPE_INT);
//$table->column('from_id', swoole_table::TYPE_STRING, 10);
//$table->column('data', swoole_table::TYPE_STRING, 64);
//$table->create();
//for ($fd = 1; $fd < 100; $fd++) {
//    $ret = $table->set($fd, array('from_id' => pow(2, 36), 'fd' => $fd, 'data' => 'data'));
//    var_dump($fd, $ret);
//}
//
//var_dump($table->get(1));
//exit;

//for ($uid = 1; $uid < 200; $uid++) {
//    $a->addVip($uid, $uid);
//}

//$q = new \Live\Lib\Elasticsearch();
//$ret = $q->indexUser();

//$ds_award = new \Live\Third\Pili();
//$ds_award->start('1', '9090');
//var_dumP($ds_award->getRecommend(0));

//$vip = new Live\Redis\Vip();
//$uid = 1;
//$vip->addWait($uid, 686);
//
//var_dump($vip->getWait($uid));
//exit;
//$my = new \Live\Controller\My();
//$my->checkIn();
//$db_balance = new \Live\Database\Balance();
//$ret = $db_balance->addByGoods(1, 5, 'ios');
//var_dump($ret);
//$data = (new \Live\Lib\Live())->stop(1);
//var_dump($data);


//$link = new \redisProxy();
//$link->connect('127.0.0.1', 6366, 0);
//
//$link->select(0);


function userLevel()
{
    var_export(\Live\Database\UserLevel::q());
}

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