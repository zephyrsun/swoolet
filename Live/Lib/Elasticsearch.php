<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/4
 * Time: 下午3:56
 */

namespace Live\Lib;

class Elasticsearch extends \Swoolet\Lib\Elasticsearch
{
    public $index = 'live';

    public function indexUser()
    {
        $ds_user = new \Live\Database\User();

        $tables = $ds_user->getAllTable();
        foreach ($tables as $table) {
            $uid = 0;

            $es = new \Live\Lib\Elasticsearch();

            $ds_user->table($table)->limit(500);

            while ($data = $ds_user->where('uid > ?', $uid)->fetchAll()) {
                foreach ($data as $row) {
                    $uid = $row['uid'];

                    $ret = $es->add('user', $uid, [
                        'uid' => $uid,
                        'nickname' => $row['nickname'],
                        'sign' => $row['sign'],
                    ]);

                    var_export($ret);
                    echo PHP_EOL;
                }
            }
        }
    }

    public function search($kw, $from = 0)
    {
        $size = 20;

        $data = [
            'query' => [
//                'query_string' => [
//                    'default_field' => 'nickname',
//                    'query' => "\"$kw\"",
//                ],
                'multi_match' => [
                    'query' => $kw,
                    'type' => 'best_fields',
                    'fields' => ['uid', 'nickname', 'sign'],
                    'analyzer' => 'standard',
                ],
            ],
            'from' => $from,
            'size' => $size,
        ];

        $ret = parent::search($data);

        $ds_user = new \Live\Database\User();
        $n = 0;
        foreach ($ret as &$row) {
            $uid = $row['_id'];
            $row = $ds_user->getShowInfo($uid, 'lv');
            $row['key'] = ++$n + $from;
        }

        return $ret;
    }

}