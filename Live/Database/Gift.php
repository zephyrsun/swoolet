<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Live\Redis\Rank;
use Live\Response;
use Swoolet\Data\PDO;

class Gift extends Basic
{
    public $cfg_key = 'db_1';

    public $key_gift = 'gift:all';

    public function __construct()
    {
        $this->option['dbname'] = 'live';

        PDO::__construct();

        $this->cache = new \Live\Redis\Gift();
    }

    public function table($key)
    {
        return PDO::table('gift');
    }

    public function getAll($limit, $force = false)
    {
        if ($force || !$ret = $this->cache->get($this->key_gift)) {
            $data = $this->table(1)->where('status', 1)->orderBy('id,sort')->fetchAll();

            $ret = [];
            foreach ($data as $row)
                $ret[$row['id']] = $row;

            $this->cache->set($this->key_gift, $ret);
        }

        if ($limit)
            return array_slice($ret, 0, $limit);

        return $ret;
    }

    public function getGift($id, $key = '')
    {
        $all = $this->getAll(0);
        $item = &$all[$id];
        if ($item) {
            if ($key)
                $item = &$item[$key];

            return $item;
        }

        return [];
    }

    public function sendGift($send_uid, $to_uid, $gift_id, $num)
    {
        $gift = $this->getGift($gift_id);
        if (!$gift)
            return Response::msg('参数错误', 1010);

        $money = $gift['money'] * $num;

        $this->beginTransaction();
        $ret = (new Balance())->sub($send_uid, $money, $gift['exp']);
        if (!$ret) {
            $this->rollback();
            return $ret;
        }

        $ret = (new MoneyLog())->add($send_uid, $to_uid, -$money, 1, "gift:{$gift_id}");
        if (!$ret) {
            $this->rollback();
            return Response::msg('送礼失败', 1015);
        }

        $ret = (new Income())->add($to_uid, $money);
        if (!$ret) {
            $this->rollback();
            return Response::msg('送礼失败', 1013);
        }

        if ($this->commit()) {
            (new Rank())->addRank($send_uid, $to_uid, $money);
        }

        if ($gift['money'] > 3e3) {
            (new \Live\Redis\Award())->addRecommend($send_uid, $gift['msg']);
        } elseif ($num >= 100) {
            (new \Live\Redis\Award())->addRecommend($send_uid, '送礼达人');
        } elseif ($num >= 666) {
            (new \Live\Redis\Award())->addRecommend($send_uid, '送礼大师');
        } elseif ($num >= 9999) {
            (new \Live\Redis\Award())->addRecommend($send_uid, '送礼至尊');
        }

        return $gift['name'];
    }

    public function sendHorn($send_uid, $to_uid)
    {
        $money = 20;

        $this->beginTransaction();
        $ret = (new Balance())->sub($send_uid, $money, $money);
        if (!$ret) {
            $this->rollback();
            return $ret;
        }

        $ret = (new MoneyLog())->add($send_uid, $to_uid, $money, 1, 'horn');
        if (!$ret) {
            $this->rollback();
            return Response::msg('发送弹幕失败', 1018);
        }

        if ($ret = $this->commit()) {
            (new Rank())->addRank($send_uid, $to_uid, $money);
        }

        return $ret;
    }
}