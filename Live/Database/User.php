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

class User extends Basic
{
    public $cfg_key = 'db_1';
    public $table_prefix = 'user_';

    public $key_user = 'user:';

    public $key_home_visit = 'h_visit:';

    public $user_level;

    CONST PF_MOBILE = 1;

    public function __construct()
    {
        $this->option['dbname'] = 'live_user';

        parent::__construct();

        $this->cache = new \Live\Redis\User();

        $this->user_level = new UserLevel();
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

            if (isset($user['city'])) {
                $this->updateUser($uid, [
                    'city' => $user['city'],
                ]);
            }

        } else {
            $uid = $this->getUID($pf, $username);

            $this->hashTable($uid);
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
        $this->hashTable($uid)->where('uid', $uid);

        $ret = parent::update($data);

        if ($ret) {
            $this->cache->del($this->key_user . $uid);

            if (isset($data['nickname'])) {
                (new \Live\Lib\Elasticsearch())->add('user', $uid, ['nickname' => $data['nickname']]);
            } elseif (isset($data['sign'])) {
                (new \Live\Lib\Elasticsearch())->add('user', $uid, ['sign' => $data['sign']]);
            }
        }

        return $ret;
    }

    public function limitUpdate($method, $uid, $data)
    {
        static $col = ['nickname' => '昵称', 'sex' => '性别', 'birthday' => '生日'];

        if ($method == 'add')
            foreach ($col as $key => $name) {
                if (isset($data[$key])) {
                    $this->cache->add("user_$key:$uid", 1, 86400 * 30);
                }
            }
        elseif ($method == 'get') {
            foreach ($col as $key => $name) {
                if (isset($data[$key]) && $this->cache->get("user_$key:$uid")) {
                    return Response::msg("\"$name\"30天内只能修改一次");
                }
            }
        }

        return true;
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
            return [];

        if ($type == 'simple') {
            $user = [
                'uid' => $user['uid'],
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar'],
                //'height' => $user['height'],
            ];
        } elseif ($type == 'lv') {
            $user = $this->isExpired($user) + [
                    'uid' => $user['uid'],
                    'nickname' => $user['nickname'],
                    'height' => $user['height'],
                    'avatar' => $user['avatar'],
                    'zodiac' => $user['zodiac'],
                    'city' => $user['city'],
                    'lv' => $this->user_level->getLv($uid)
                ];
        } elseif ($type == 'more') {

            $user['mobile'] = (string)$user['mobile'];

            $user += $this->isExpired($user) + [
                    'lv' => $this->user_level->getLv($uid),
                ];

            unset($user['vip_expire'], $user['tycoon_expire']);
        }

        return $user;
    }

    public function isExpired($user)
    {
        $ts = \Swoolet\App::$ts;

        return [
            'is_vip' => $user['vip_expire'] > $ts ? 1 : 0,
            'is_tycoon' => $user['tycoon_expire'] > $ts ? 1 : 0,
        ];
    }

    public function isVip($user)
    {
        is_array($user) or $user = $this->getUser($user);//$user as $uid

        return $user['vip_expire'] > \Swoolet\App::$ts ? 1 : 0;
    }

    public function getUserInfo($uid, $follow_uid)
    {
        $user = $this->getShowInfo($uid, 'more');

        $db_fan = new Fan();

        return $user + [
            'income' => (new Income())->getIncome($uid),
            'sent' => (new Balance())->get($uid, 'sent'),
            'follow' => (new Follow())->getCount($uid),
            'fan' => $db_fan->getCount($uid),
            'is_follow' => $db_fan->isFollow($follow_uid, $uid),
        ];
    }

    public function getUser($uid)
    {
        $user = $this->getWithCache($this->key_user . $uid, function () use ($uid) {
            if ($user = $this->hashTable($uid)->where('uid', $uid)->fetch()) {
                unset($user['username'], $user['create_ts']);

                $user['city'] = \Live\Lib\Utility::generateCity($user['city']);
            }

            return $user ? $user : [];
        });

        return $user;
    }

    public function getByUsername($pf, $username)
    {
        return parent::table('increment')
            ->where('pf=? AND username=?', [$pf, $username])
            ->fetch();
    }

    public function getUID($pf, $username)
    {
        return parent::table('increment')->insert([
            'pf' => $pf,
            'username' => $username,
        ]);
    }

    public function addVisit($uid, $visit_uid)
    {
        if ($uid == $visit_uid)
            return;

        return $this->cache->link->zAdd($this->key_home_visit . $uid, \Swoolet\App::$ts, $visit_uid);
    }

    public function getVisit($uid, $start, $limit)
    {
        //$list = $this->cache->link->lRange($this->key_home_visit . $uid, $start, $limit - 1);
        $list = $this->cache->revRange($this->key_home_visit . $uid, $start, $limit, false);

        $i = 0;
        foreach ($list as &$uid) {
            $uid = $this->getShowInfo($uid, 'lv');
            $uid['key'] = $start + $i++;
        }

        return $list;
    }
}