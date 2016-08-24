<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Live\Response;
use Swoolet\App;

class UserLevel extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'user_';

    public $key_ext = 'user_ext:';

    static public $exp = [
        1 => 0,
        2 => 10,
        3 => 40,
        4 => 100,
        5 => 200,
        6 => 400,
        7 => 700,
        8 => 1100,
        9 => 1600,
        10 => 2200,
        11 => 2900,
        12 => 3700,
        13 => 4600,
        14 => 5600,
        15 => 6700,
        16 => 7900,
        17 => 9200,
        18 => 10600,
        19 => 12100,
        20 => 13700,
        21 => 15400,
        22 => 19000,
        23 => 22800,
        24 => 26800,
        25 => 31000,
        26 => 35400,
        27 => 44600,
        28 => 54200,
        29 => 64200,
        30 => 74600,
        31 => 85400,
        32 => 107800,
        33 => 131000,
        34 => 155000,
        35 => 179800,
        36 => 205400,
        37 => 311000,
        38 => 419800,
        39 => 531800,
        40 => 647000,
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

        $ret = $this->table($uid)->replace([
            'uid' => $uid,
            'exp' => $new_exp,
            'lv' => $lv,
        ]);

        return $new_exp;
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

        return $lv ? (int)$lv : 1;
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
            $mid = (int)(($low + $high) / 2);
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
        $q = 4;
        $i = 0;
        $n = 100;
        $f = 100;
        $a = [];
        while ($i < 40) {

            $a[$q++] = $n;

            if ($q > 36)
                $f = 3200;
            elseif ($q > 31)
                $f = 800;
            elseif ($q > 26) {
                $f = 400;
            } elseif ($q > 21) {
                $f = 200;
            }

            $n += $f * ++$i;
        }

        return $a;
    }
}