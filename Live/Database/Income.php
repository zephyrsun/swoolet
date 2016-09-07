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
 * 主播收入
 * @package Live\Database
 */
class Income extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'user_';

    public function __construct()
    {
        $this->option['dbname'] = 'live_income';

        PDO::__construct();

        $this->cache = new UserExt();
    }

    public function getIncome($uid)
    {
        return $this->cache->getWithCallback($uid, 'income', function () use ($uid) {
            return (int)$this->hashTable($uid)->select('income')->where('uid', $uid)->fetchColumn();
        });
    }

    public function add($uid, $money)
    {
        if ($money <= 0)
            return Response::msg('参数错误', 1011);

        $ret = $this->hashTable($uid)->where('uid', $uid)->update("income = income + $money, total = total + $money");
        if (!$ret) {
            $ret = $this->hashTable($uid)->insert([
                'uid' => $uid,
                'income' => $money,
                'total' => $money,
            ]);
        }

        if ($ret) {
            $this->cache->del($uid, 'income');
            return $ret;
        }

        return Response::msg('数据更新失败', 1019);
    }
}