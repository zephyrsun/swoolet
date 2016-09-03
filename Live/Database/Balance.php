<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;

use Live\Redis\UserExt;
use Live\Redis\Award;
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

    public function get($uid, $key = 'sent')
    {
        return $this->cache->getWithCallback($uid, $key, function () use ($uid) {
            $ret = $this->table($uid)->select('balance,sent,charge')->where('uid', $uid)->fetch();

            return $ret ? $ret : ['balance' => 0, 'sent' => 0, 'charge' => 0];
        });
    }

    public function add($uid, $balance, $charge, $log = true)
    {
        $ret = $this->table($uid)->where('uid', $uid)->update("balance = balance + $balance, charge = charge + $charge");
        if (!$ret) {
            $ret = $this->table($uid)->insert([
                'uid' => $uid,
                'balance' => $balance,
                'charge' => $charge,
            ]);
        }

        if (!$ret)
            return Response::msg('数据更新失败', 1014);

        $this->cache->del($uid, 'balance', 'sent', 'charge');

        if ($log) {
            $ret = (new MoneyLog())->add($uid, $uid, $balance, 1, "charge:{$charge}");
            if (!$ret)
                return Response::msg('数据插入失败', 1015);
        }

        return $ret;
    }

    public function addByGoods($uid, $goods_id, $pf)
    {
        $goods = (new Goods())->getGoods($goods_id, $pf);

        $coin = $goods['coin'];
        $exp = $goods['exp'];
        $charge = $goods['money'];//充值总额

        $ret = $this->add($uid, $coin, $charge);

        if ($ret) {

            (new UserLevel())->add($uid, $exp);

            $this->cache->del($uid, 'sent', 'charge');
            $money = $this->get($uid, 'charge');

            $db_user = (new User());

            $vip_day = $goods['vip_day'];
            $tycoon_day = $goods['tycoon_day'];

            //额外奖励加成：
            //充值满98元，奖励15天会员，查看会员特权
            //充值满298元，奖励45天会员，查看会员特权
            //充值满598元，奖励90天会员，查看会员特权
            $award_vip = [598 => 90, 298 => 45, 98 => 15];
            foreach ($award_vip as $threshold => $award_day) {
                if (($money >= $threshold) && ($money - $charge < $threshold)) {
                    // $award_key = 'charge_award_' . $threshold;
                    // if (!$this->cache->get($uid, $award_key))
                    $vip_day = $award_day;

                    (new \Live\Redis\Award())->addRecommend($uid, "充值获得{$award_day}天会员");

                    break;
                }
            }

            if ($vip_day) {
                $db_user->incrExpire($uid, 'vip_expire', $vip_day);
                //if ($award_key)
                //    $this->cache->set($uid, $award_key, $award_day);

                //充值vip有机会抽取100看币
                if ($charge >= 98) {
                    (new Award())->addWait($uid, $charge);
                }
            }

            if ($tycoon_day) {
                $db_user->incrExpire($uid, 'tycoon_expire', $tycoon_day);

                $msg = $tycoon_day > 30 ? '此人超级土豪' : '此人特别土豪';
                (new \Live\Redis\Award())->addRecommend($uid, $msg);
            }
        }

        return $ret;
    }

    public function sub($uid, $balance, $exp)
    {
        if ($balance <= 0)
            return Response::msg('参数错误', 1016);

        $ret = $this->table($uid)->where('uid = ? AND balance >= ?', [$uid, $balance])
            ->update("balance = balance - $balance, sent = sent + $balance");

        if ($ret) {
            $this->cache->del($uid, 'balance', 'sent');
            return $ret;
        }

        return Response::msg('余额不足', 1017);
    }
}