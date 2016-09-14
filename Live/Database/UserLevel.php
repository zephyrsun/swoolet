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
        21 => 17100,
        22 => 20700,
        23 => 24500,
        24 => 28500,
        25 => 32700,
        26 => 41500,
        27 => 50700,
        28 => 60300,
        29 => 70300,
        30 => 80700,
        31 => 102300,
        32 => 124700,
        33 => 147900,
        34 => 171900,
        35 => 196700,
        36 => 299100,
        37 => 404700,
        38 => 513500,
        39 => 625500,
        40 => 740700,
        41 => 9999999,
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

        $new_exp = min($new_exp, end(self::$exp));

        $lv = self::exp2lv($new_exp);
        $this->cache->set($uid, 'lv', $lv);

        $ret = $this->hashTable($uid)->replace([
            'uid' => $uid,
            'exp' => $new_exp,
            'lv' => $lv,
        ]);

        return $new_exp;
    }

    public function reCache($uid)
    {
        $data = $this->hashTable($uid)->select('exp,lv')->where('uid', $uid)->fetch();
        if ($data) {
            $this->cache->mSet($uid, $data);
        }

        return $data;
    }

    public function getExp($uid)
    {
        return $this->getLv($uid, 'exp');
    }

    public function getLv($uid, $key = 'lv')
    {
        if (!$ret = $this->cache->get($uid, $key)) {
            if ($data = $this->reCache($uid))
                $ret = $data[$key];
        }

        return $ret ? (int)$ret : 1;
    }

    public function getLvAndExp($uid)
    {
        $ret = $this->cache->mget($uid, ['exp', 'lv']);
        if (!$ret['exp'] && !$ret = $this->reCache($uid)) {
            $ret = [
                'exp' => 0,
                'lv' => 1,
            ];
        }

        end(self::$exp);
        $max_lv = key(self::$exp);
        $next_lv = $ret['lv'] < $max_lv ? $ret['lv'] + 1 : $max_lv;

        $ret['next_exp'] = self::$exp[$next_lv];

        return $ret;
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

            if ($q > 35)
                $f = 3200;
            elseif ($q > 30)
                $f = 800;
            elseif ($q > 25) {
                $f = 400;
            } elseif ($q > 20) {
                $f = 200;
            }

            $n += $f * ++$i;
        }

        return $a;
    }
}