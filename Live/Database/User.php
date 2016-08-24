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

    public function login($pf, $username, $user = [])
    {
        $user_map = $this->getByUsername($pf, $username);
        if ($user_map) {
            $uid = $user_map['uid'];

            /*
            if ($avatar) {
                $this->updateUser([
                    'avatar' => $avatar,
                ], $uid);
            }
            */

        } else {
            $uid = $this->getUID($pf, $username);

            $this->table($uid);
            $this->insert($user + [
                    'uid' => $uid,
                    'username' => $username,
                    'nickname' => '',
                    'height' => 0,
                    'birthday' => '0000-00-00',
                    'zodiac' => '',
                    'sign' => '',
                    'avatar' => '',
                    'create_ts' => \Swoolet\App::$ts,
                ]);
        }

        return $this->getUser($uid);
    }

    public function updateUser($uid, $data)
    {
        $this->table($uid)->where('uid', $uid);

        return parent::update($data);
    }

    public function incrExpire($uid, $field, $day)
    {
        $data = $this->getUser($uid);
        $day *= 86400;

        $old_num = $data[$field];
        if ($old_num) {
            $day += $old_num;
        } else {
            $day += \Swoolet\App::$ts;
        }

        return $this->updateUser($uid, [$field => $day]);
    }

    public function getShowInfo($uid, $type = 'simple')
    {
        $user = $this->getUser($uid);
        if (!$user)
            return false;

        if ($type == 'simple') {
            $ret = [
                'uid' => $user['uid'],
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar'],
                //'height' => $user['height'],
            ];
        } elseif ($type == 'lv') {
            $ret = [
                'uid' => $user['uid'],
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar'],
                'lv' => (new UserLevel())->getLv($uid)
            ];
        }

        return $ret;
    }

    public function getUser($uid)
    {
        $user = $this->getWithCache($this->key_user . $uid, function () use ($uid) {
            if ($user = $this->table($uid)->where('uid', $uid)->fetch()) {
                unset($user['username'], $user['create_ts']);

                $user['city'] or $user['city'] = '看好星球';
            }

            return $user ? $user : [];
        });

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