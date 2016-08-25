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

class RoomAdmin extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'room_admin';

    public $key_admin = 'room_admin:';
    public $key_silence = 'silence:';

    public function __construct()
    {
        $this->option['dbname'] = 'live';

        parent::__construct();

        $this->cache = new \Live\Redis\RoomAdmin();
    }

    public function table($key)
    {
        PDO::table($this->table_prefix);

        return $this;
    }

    public function add($uid, $admin_uid)
    {
        $admin_uid = (string)$admin_uid;

        $admins = $this->getRoomAdmin($uid);
        if (count($admins) > 5)
            return Response::msg('管理员最多只能有5位，删除其他管理员才能添加！');
        elseif (in_array($admin_uid, $admins, true))
            return true;

        $ret = $this->table($uid)->insert([
            'uid' => $uid,
            'admin_uid' => $admin_uid,
        ], 'INSERT IGNORE INTO');

        if ($ret)
            $this->cache->del($this->key_admin . $uid);

        return $ret;
    }

    public function del($uid, $admin_uid)
    {
        $ret = $this->table($uid)->where('uid=? AND admin_uid=?', [$uid, $admin_uid])->delete();
        if ($ret)
            $this->cache->del($this->key_admin . $uid);

        return $ret;
    }

    public function silenceUser($room_id, $uid)
    {
        return $this->cache->link->set($this->key_silence . "{$uid}_{$room_id}", 1, 7200);
    }

    public function isSilence($room_id, $uid)
    {
        return $this->cache->link->get($this->key_silence . "{$uid}_{$room_id}");
    }

    public function isAdmin($uid, $admin_uid)
    {
        return $this->cache->link->sIsMember($this->key_admin . $uid, $admin_uid);
    }

    /**
     * 获取我的管理员
     *
     * @param $uid
     * @return array
     */
    public function getRoomAdmin($uid)
    {
        $key = $this->key_admin . $uid;

        if (!$data = $this->cache->link->sMembers($key)) {

            $data = $this->table($uid)->select('admin_uid')
                ->where('uid=?', $uid)->fetchAll(\PDO::FETCH_COLUMN, 0);

            if ($data) {
                array_unshift($data, $key);

                call_user_func_array([$this->cache->link, 'sAdd'], $data);

                $this->cache->expire($key, $this->timeout);

                //get again
                $data = $this->cache->link->sMembers($key);
            }
        }

        return $data;
    }
}