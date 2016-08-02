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

    public function getAllGift($force = false)
    {
        if ($force || !$ret = $this->cache->get($this->key_gift)) {
            $data = $this->table(1)->fetchAll();

            $ret = [];
            foreach ($data as $row)
                $ret[$row['id']] = $row;

            $this->cache->set($this->key_gift, $ret);
        }

        return $ret;
    }

    public function getGift($gift_id, $key = '')
    {
        $all = $this->getAllGift();
        $gift = &$all[$gift_id];
        if ($gift && $gift['status'] == 1) {
            if ($key)
                $gift = &$gift[$key];

            return $gift;
        }

        return null;
    }

    public function sendGift($uid, $to_uid, $gift_id)
    {
        $money = $this->getGift($gift_id, 'money');
        if (!$money)
            return Response::msg('参数错误', 1010);

        $this->beginTransaction();
        $ret = (new Balance())->sub($uid, $money);
        if (!$ret)
            return $ret;

        $ret = (new MoneyLog())->add($uid, $to_uid, $money, 1, $gift_id);
        if (!$ret) {
            $this->rollback();
            return Response::msg('送礼失败',1015);
        }

        $ret = (new Income())->add($to_uid, $money);
        if (!$ret) {
            $this->rollback();
            return Response::msg('送礼失败',1013);
        }

        $this->commit();
        return $ret;
    }
}