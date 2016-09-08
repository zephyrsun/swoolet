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
                        'nickname' => $row['nickname'],
                        'sign' => $row['sign'],
                    ]);
                }
            }
        }
    }

    public function search($kw, $from = 0)
    {
        $size = 2;

        $data = [
            'query' => [
//                'query_string' => [
//                    'default_field' => 'nickname',
//                    'query' => "\"$kw\"",
//                ],
                'multi_match' => [
                    'query' => $kw,
                    'type' => 'best_fields',
                    'fields' => ['nickname', 'sign'],
                    'analyzer' => 'keyword',
                ],
            ],
            'from' => $from,
            'size' => $size,
        ];

        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $ret = $this->curl->post("http://{$this->option['host']}/{$this->index}/_search", $data);
        $ret = \json_decode($ret, true);
        $ret = $ret['hits']['hits'];

        $ds_user = new \Live\Database\User();
        $n = 0;
        foreach ($ret as &$row) {
            $uid = $row['_id'];
            $row = $ds_user->getShowInfo($uid);
            $row['key'] = ++$n + $from;
        }

        return $ret;
    }

}