<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Live\Response;

class UserLevel extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'user_';

    public $key_ext = 'user_ext:';

    static public $exp = [
        1 => 10,
        2 => 50,
        3 => 100,
        4 => 200,
        5 => 400,
        6 => 700,
        7 => 1100,
        8 => 1600,
        9 => 2200,
        10 => 2900,
        11 => 3700,
        12 => 4600,
        13 => 5600,
        14 => 6700,
        15 => 7900,
        16 => 9200,
        17 => 10600,
        18 => 12100,
        19 => 13700,
        20 => 15400,
        21 => 17200,
        22 => 19100,
        23 => 21100,
        24 => 23200,
        25 => 25400,
        26 => 27700,
        27 => 30100,
        28 => 32600,
        29 => 35200,
        30 => 40600,
        31 => 46200,
        32 => 52000,
        33 => 58000,
        34 => 64200,
        35 => 70600,
        36 => 77200,
        37 => 84000,
        38 => 91000,
        39 => 98200,
        40 => 105600,
    ];

    public function __construct()
    {
        $this->option['dbname'] = 'live_user_exp';

        parent::__construct();

        $this->cache = new \Live\Redis\UserExt();
    }

    public function add($uid, $exp)
    {
        if ($exp <= 0)
            return false;

        $new_exp = $this->cache->incr($uid, 'exp', $exp);
        if (!$new_exp)
            return Response::msg('服务器错误', 1030);

        $lv = self::exp2lv($new_exp);
        $this->cache->set($uid, 'lv', $lv);

        if ($new_exp == $exp) {
            //new
            $ret = $this->table($uid)->insert([
                'uid' => $uid,
                'exp' => $new_exp,
                'lv' => $lv,
            ]);
        } elseif ($new_exp) {
            $ret = $this->table($uid)->where('uid', $uid)->update([
                'exp' => $new_exp,
                'lv' => $lv,
            ]);
        } else {
            return Response::msg('服务器错误', 1031);
        }

        return $ret;
    }

    public function getLv($uid)
    {
        if (!$lv = $this->cache->get($uid, 'lv')) {

            $data = $this->table($uid)->select('exp,lv')->where('uid', $uid)->fetch();
            if ($data) {
                $this->cache->mSet($uid, $data);
                $lv = $data['lv'];
            }
        }

        return (int)$lv;
    }

    /**
     * @param $exp
     * @return bool|float
     */
    static public function exp2lv($exp)
    {
        $arr = self::$exp;

        $lv = 0;
        $low = 0;
        $high = count($arr);

        while ($low <= $high) {
            $mid = floor(($low + $high) / 2);
            $mid_exp = $arr[$mid];

            if ($mid_exp > $exp)
                $high = $mid - 1;
            elseif ($mid_exp <= $exp) {
                $low = $mid + 1;
                $lv = $mid;
            }
        }

        #查找失败
        return $lv;
    }

    static public function q()
    {
        $q = 3;
        $i = 0;
        $n = 100;
        $f = 100;
        $a = [];
        while ($i < 40) {

            $a[$q++] = $n;

            if ($q >= 30)
                $f = 200;

            $n += $f * ++$i;
        }
    }
}