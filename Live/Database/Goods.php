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
    public function getList($pf, $type, $channel, $force = false)
    {
        $key = $this->key_goods . 'all';
        if ($force || !$list = $this->cache->get($key)) {

            $this->table(1)->select('id,channel,type,coin,money,exp,vip_day,tycoon_day')->orderBy('money ASC');

            $data = $this->fetchAll();

            $list = [];
            foreach ($data as $row) {
                $list[$row['id']] = $row;
            }

            if ($list)
                $this->cache->set($key, $list);
        }

        if ($type > 0) {
            $new_list = [];

            foreach ($list as $row) {
                if ($pf == 'ios')
                    $row['coin'] *= 0.7;

                if ($row['channel'] == $channel && $row['type'] == $type) {
                    unset($row['channel'], $row['type'], $row['vip_day'], $row['tycoon_day']);
                    $new_list[] = $row;
                }
            }

            return $new_list;
        }

        return $list;
    }

    public function getGoods($id, $pf)
    {
        //$ret = $this->table(1)->where('id', $id)->fetch();

        $list = $this->getList($pf, 0, 0);

        $row = &$list[$id];
        if ($row && $pf == 'ios')
            $row['coin'] *= 0.7;

        return $row;
    }
}