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

/**
 * 主播收入
 * @package Live\Database
 */
class Income extends Basic
{
    public $cfg_key = 'db_1';

    public function __construct()
    {
        $this->option['dbname'] = 'live_income';

        PDO::__construct();

        //$this->cache = new \Live\Redis\User();
    }

    public function add($uid, $money)
    {
        if ($money <= 0)
            return Response::msg('参数错误', 1011);

        $ret = $this->table($uid)->where('uid', $uid)->update("money = money + $money");
        if (!$ret) {
            $ret = $this->insert([
                'uid' => $uid,
                'money' => $money,
            ]);

            if (!$ret)
                return Response::msg('数据更新失败', 1012);
        }

        return $ret;
    }

    public function sub($uid, $money)
    {
        if ($money < 0)
            $money = -$money;
        elseif ($money == 0)
            return Response::msg('参数错误', 1012);

        $ret = $this->table($uid)->where('uid = ? AND money >= ?', [$uid, $money])
            ->update("money = money - $money");

        if (!$ret)
            return Response::msg('余额不足', 1012);

        return $ret;
    }
}