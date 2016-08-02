<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Swoolet\Data\PDO;

/**
 * 用户余额
 * @package Live\Database
 */
class Balance extends Income
{
    public $cfg_key = 'db_1';

    public function __construct()
    {
        $this->option['dbname'] = 'live_balance';

        PDO::__construct();

        //$this->cache = new \Live\Redis\User();
    }
}