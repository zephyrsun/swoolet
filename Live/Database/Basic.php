<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Swoolet\Data\PDO;

class Basic extends PDO
{
    public $cache;

    public $table_prefix = 'user_';
    public $table_mod = 1e6;

    public function table($key)
    {
        $mod = (int)($key / $this->table_mod);

        PDO::table($this->table_prefix . $mod);

        return $this;
    }

}