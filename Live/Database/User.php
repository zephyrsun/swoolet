<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/29
 * Time: 下午2:51
 */

namespace Live\Database;


use Swoolet\Data\PDO;

class User extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'user_';

    public $key_user = 'user:';

    CONST PF_MOBILE = 1;

    public function __construct()
    {
        $this->option['dbname'] = 'live_user';

        parent::__construct();

        $this->cache = new \Live\Redis\User();
    }

    public function login($pf, $username, $avatar = '')
    {
        $user_map = $this->getByUsername($pf, $username);
        if ($user_map) {
            $uid = $user_map['uid'];

            if ($avatar) {
                $this->updateUser([
                    'avatar' => $avatar,
                ], $uid);
            }

        } else {
            $uid = $this->getUID($pf, $username);

            $this->table($uid);
            $this->insert([
                'uid' => $uid,
                'username' => $username,
                'avatar' => $avatar,
                'birthday' => '0000-00-00',
                'create_ts' => \Swoolet\App::$ts,
            ]);
        }
        $user = $this->getUser($uid);

        return $user;
    }

    public function updateUser($data, $uid)
    {
        $this->table($uid);

        return parent::update($data);
    }

    public function getUser($uid)
    {
        if (!$user = $this->cache->get($this->key_user . $uid)) {
            if ($user = $this->table($uid)->where('uid', $uid)->fetch()) {

                unset($user['username'], $user['create_ts']);

                $this->cache->set($this->key_user . $uid, $user, $this->timeout);
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
}