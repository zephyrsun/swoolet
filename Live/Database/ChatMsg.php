<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;

use Swoolet\App;
use Swoolet\Data\PDO;

class ChatMsg extends Basic
{
    public $cfg_key = 'db_1';

    public $table_prefix = 'user_';

    public function __construct()
    {
        $this->option['dbname'] = 'live_chat_msg';

        parent::__construct();

        //$this->cache = new \Live\Redis\User();
    }

    public function add($uid, $from_uid, $msg)
    {
        return $this->table($uid)->insert([
            'uid' => $uid,
            'from_uid' => $from_uid,
            'msg' => $msg,
            'ts' => \Swoolet\App::$ts,
        ]);
    }

    public function getMsg($uid)
    {
        $this->table($uid)->select('id,from_uid,msg,ts')
            ->orderBy('id DESC')->where('uid', $uid);

        return $this->fetchAll();
    }

    public function markAsRead($uid)
    {
        return $this->table($uid)->where('uid', $uid)->delete();
    }
}