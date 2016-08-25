<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Swoolet\Data\PDO;

class Goods extends Basic
{
    const TYPE_CHARGE = 1;
    const TYPE_VIP = 2;

    public $cfg_key = 'db_1';

    public $key_goods = 'goods:';

    public function __construct()
    {
        $this->option['dbname'] = 'live';

        PDO::__construct();

        $this->cache = new \Live\Redis\Goods();
    }

    public function table($key)
    {
        return PDO::table('goods');
    }

    /**
     * @param $channel
     * @param $type
     *        - 1:普通充值
     *        - 2:开通会员
     * @param string $pf
     * @param bool $force
     * @return array
     */
    public function getChannel($channel, $type, $pf = '', $force = false)
    {
        $key = $this->key_goods . $channel;
        if ($force || !$ret = $this->cache->get($key)) {
            $data = $this->table(1)->select('id,coin,money,exp,vip_day,tycoon_day')->where('channel', $channel)->orderBy('money ASC')->fetchAll();

            $ret = [];
            foreach ($data as $row) {
                $ret[$row['id']] = $row;
            }

            if ($ret)
                $this->cache->set($key, $ret);
        }

        $list = [];
        foreach ($ret as $row) {

            if ($pf == 'ios')
                $row['coin'] *= 0.7;

            if ($row['type'] == $type) {
                unset($row['vip_day'], $row['tycoon_day']);
                $list[] = $row;
            }
        }

        return $list;
    }

    public function getGoods($id, $pf = '')
    {
        $ret = $this->table(1)->where('id', $id)->fetch();
        if ($ret && $pf == 'ios')
            $ret['coin'] *= 0.7;

        return $ret;
    }
}