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

class ReportUser extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'report_user';

    //public $key_live = 'live:';

    public function __construct()
    {
        $this->option['dbname'] = 'live';

        parent::__construct();
    }

    public function table($key)
    {
        PDO::table($this->table_prefix);

        return $this;
    }

    public function add($uid, $to_uid, $reason)
    {
        if ($uid == $to_uid)
            return Response::msg('自己举报自己是一种怎样的体验？', 1037);

        return $this->table($uid)->insert([
            'uid' => $uid,
            'to_uid' => $to_uid,
            'reason' => $reason,
            'ts' => \Swoolet\App::$ts,
        ], 'REPLACE INTO');
    }
}