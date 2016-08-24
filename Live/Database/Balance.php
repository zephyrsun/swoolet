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

    public function get($uid, $key = 'sent')
    {
        return $this->cache->getWithCallback($uid, $key, function () use ($uid) {
            return $this->table($uid)->select('sent,charge')->where('uid', $uid)->fetch();
        });
    }

    public function add($uid, $goods_id, $pf)
    {
        $goods = (new Goods())->getGoods($goods_id, $pf);

        $coin = $goods['coin'];
        $exp = $goods['exp'];
        $charge = $goods['money'];//充值总额

        $ret = $this->table($uid)->where('uid', $uid)->update("balance = balance + $coin, charge = charge + $charge");
        if (!$ret) {
            $ret = $this->table($uid)->insert([
                'uid' => $uid,
                'balance' => $coin,
                'charge' => $charge,
            ]);
        }

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
                if ($money >= $threshold && $money - $charge < $threshold) {
                    // $award_key = 'charge_award_' . $threshold;
                    // if (!$this->cache->get($uid, $award_key))
                    $vip_day = $award_day;

                    break;
                }
            }

            if ($vip_day) {
                $db_user->incrExpire($uid, 'vip_expire', $vip_day);
                //if ($award_key)
                //    $this->cache->set($uid, $award_key, $award_day);
            }

            if ($tycoon_day) {
                $db_user->incrExpire($uid, 'tycoon_expire', $tycoon_day);
            }

            return $ret;
        }

        return Response::msg('数据更新失败', 1014);
    }

    public function sub($uid, $balance, $exp)
    {
        if ($balance <= 0)
            return Response::msg('参数错误', 1016);

        $ret = $this->table($uid)->where('uid = ? AND balance >= ?', [$uid, $balance])
            ->update("balance = balance - $balance, sent = sent + $balance");

        if ($ret) {
            $this->cache->del($uid, 'sent');
            return $ret;
        }

        return Response::msg('余额不足', 1017);
    }
}