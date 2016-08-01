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
    public $cache;

    public $cfg_key = 'db_1';
    public $timeout = 86400 * 3;

    //public $table_name = 'user_0';
    public $table_prefix = 'user_';
    public $table_mod = 1e6;

    public $rd_user = 'user:';

    CONST PF_MOBILE = 1;

    public function __construct()
    {
        $this->option['dbname'] = 'live_user';

        parent::__construct();

        $this->cache = new \Live\Redis\User();
    }

    public function login($pf, $username, $avatar = '')
    {
        $user = $this->getByUsername($pf, $username);
        if ($user) {
            $uid = $user['uid'];

            $this->updateUser([
                'avatar' => $user['avatar'] ? $user['avatar'] : $avatar,
            ], $uid);

        } else {
            $uid = $this->getUID($pf, $username);

            $this->table($uid);
            $this->insert([
                'uid' => $uid,
                'username' => $username,
                'avatar' => $avatar,
                'birthday' => '0000-00-00',
                'create_ts' => \APP_TS,
            ]);
        }
        $user = $this->getUser($uid);

        return [
            'user' => $user,
            'full' => $user['birthday'] != '0000-00-00',
        ];
    }

    public function updateUser($data, $uid)
    {
        $this->table($uid);

        return parent::update($data);
    }

    public function getUser($uid)
    {
        if (!$user = $this->cache->get($this->rd_user . $uid)) {
            if ($user = $this->table($uid)->where('uid', $uid)->fetch()) {

                unset($user['username'], $user['create_ts']);

                $this->cache->set($this->rd_user . $uid, $user, $this->timeout);
            }
        }

        return $user;
    }

    public function getByUsername($pf, $username)
    {
        return PDO::table('user_increment')
            ->where('pf=? AND username=?', [$pf, $username])
            ->fetch();
    }

    public function getUID($pf, $username)
    {
        return PDO::table('user_increment')->insert([
            'pf' => $pf,
            'username' => $username,
        ]);
    }

    public function table($key)
    {
        $mod = (int)($key / $this->table_mod);

        PDO::table($this->table_prefix . $mod);

        return $this;
    }

}