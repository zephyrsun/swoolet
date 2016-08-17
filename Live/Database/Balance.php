<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;

use Live\Redis\UserExt;
use Live\Response;
use Swoolet\Data\PDO;

/**
 * 用户余额
 * @package Live\Database
 */
class Balance extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'user_';

    public function __construct()
    {
        $this->option['dbname'] = 'live_balance';

        PDO::__construct();

        $this->cache = new UserExt();
    }

    public function getSent($uid)
    {
        return $this->cache->getWithCallback($uid, 'sent', function () use ($uid) {
            return (int)$this->table($uid)->select('sent')->where('uid', $uid)->fetchColumn();
        });
    }

    public function add($uid, $money)
    {
        if ($money <= 0)
            return Response::msg('参数错误', 1011);

        $ret = $this->table($uid)->where('uid', $uid)->update("balance = balance + $money");
        if (!$ret) {
            $ret = $this->table($uid)->insert([
                'uid' => $uid,
                'balance' => $money,
            ]);
        }

        if ($ret) {
            $this->cache->del($uid, 'sent');
            return $ret;
        }

        return Response::msg('数据更新失败', 1014);
    }

    public function sub($uid, $money, $exp)
    {
        if ($money < 0)
            $money = -$money;
        elseif ($money == 0)
            return Response::msg('参数错误', 1016);

        $ret = $this->table($uid)->where('uid = ? AND balance >= ?', [$uid, $money])
            ->update("balance = balance - $money, sent = sent + $money");

        if ($ret) {
            $this->cache->del($uid, 'sent');

            (new UserLevel())->add($uid, $exp);
            return $ret;
        }

        return Response::msg('余额不足', 1017);
    }
}