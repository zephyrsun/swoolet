<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Swoolet\Lib;

use Swoolet\App;

class Elasticsearch
{
    public $option = ['host' => '127.0.0.1:9200'];
    public $index;
    public $curl;

    public function __construct($index = '')
    {
        $this->option = App::getConfig('elasticsearch') + $this->option;

        if ($index)
            $this->index = $index;

        $this->curl = new CURL();
    }

    public function add($type, $id, $data)
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this->curl->put("http://{$this->option['host']}/{$this->index}/$type/$id", $data);
    }
}