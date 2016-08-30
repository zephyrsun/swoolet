<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;

use Live\Response;
use Swoolet\Data\PDO;

class MoneyLog extends Basic
{
    public $cfg_key = 'db_1';

    public function __construct()
    {
        $this->option['dbname'] = 'live_money_log';

        parent::__construct();

        //$this->cache = new \Live\Redis\User();
    }

    public function table($key)
    {
        return PDO::table('m_2016');
    }

    public function addOrder($uid, $gift_id, $pf)
    {
        $goods = (new Goods())->getGoods($gift_id, $pf);

        microtime(true);

        $id = (new MoneyLog())->add($uid, $uid, $goods['money'], 0, "alipay:$gift_id:{$goods['money']}");
        if (!$id)
            return Response::msg('充值失败', 1047);

        return $goods + [
            'trade_no' => '21' . date('Ymd', \Swoolet\App::$ts) . $id,
        ];
    }

    public function add($uid, $to_uid, $money, $status = 1, $data = '')
    {
        if (is_array($data))
            $data = json_encode($data, \JSON_UNESCAPED_UNICODE);

        return $this->table($uid)->insert([
            'uid' => $uid,
            'to_uid' => $to_uid,
            'money' => $money,
            'status' => $status,
            'data' => $data,
            'ts' => \Swoolet\App::$ts,
        ]);
    }
}