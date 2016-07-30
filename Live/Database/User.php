<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Swoolet\Data\PDO;

class User extends PDO
{
    public $cfg_key = 'db_1';

    public $table_name = 'user_0';

    public $cache;
    public $timeout = 86400 * 3;
    public $key_user = 'user:';

    public function __construct()
    {
        $this->option['dbname'] = 'live_user';

        parent::__construct();

        $this->cache = new \Live\Redis\User();
    }

    public function login($username, $login_pf, $avatar = '')
    {
        $user = $this->getByUsername($username);
        if ($user) {
            $uid = $user['uid'];
            $this->update([
                'avatar' => $user['avatar'] ? $user['avatar'] : $avatar,
            ]);
        } else {
            $uid = $this->insert([
                'username' => $username,
                'avatar' => $avatar,
                'birthday' => '0000-00-00',
                'login_pf' => $login_pf,
                'create_ts' => \APP_TS,
            ]);
        }

        $user = $this->getUser($uid);

        return [
            'user' => $user,
            'full' => (bool)$user['avatar'],
        ];
    }

    public function getByUsername($username)
    {
        return $this->where('username', $username)->fetch();
    }

    public function getUser($uid)
    {
        if (!$user = $this->cache->get($this->key_user . $uid)) {
            if ($user = $this->where('uid', $uid)->fetch()) {

                unset($user['username'], $user['login_pf'], $user['create_ts']);

                $this->cache->set($this->key_user . $uid, $user, $this->timeout);
            }
        }

        return $user;
    }

}