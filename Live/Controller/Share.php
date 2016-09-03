<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;

class Share extends Basic
{
    public function callback()
    {
        $data = parent::getValidator()->required('token')->required('room_id')->getResult();
        if (!$data)
            return $data;

        $token_uid = $data['token_uid'];

        $ret = (new \Live\Redis\Share())->setShared($token_uid);
        if ($ret) {
            $exp = mt_rand(8, 30);

            (new \Live\Database\UserLevel())->add($token_uid, $exp);

            $conn = \Server::$conn;
            $conn->sendToRoom($data['room_id'], $token_uid, [
                't' => \Live\Lib\Conn::TYPE_ROOM_BROADCAST,
                'user' => (new \Live\Database\User())->getShowInfo($token_uid, 'simple'),
                'msg' => "[nickname]分享了直播，得到{$exp}经验",
            ]);

            $base = 10;
            if ($exp > $base) {
                $extra = $exp - $base;

                (new \Live\Redis\Award())->addRecommend($token_uid, "分享获得{$exp}经验");

                return Response::msg("恭喜您获得{$base}经验以及主播幸运加成{$extra}经验，总共{$exp}经验的分享奖励！");
            }

            return Response::msg("恭喜您获得{$exp}经验的分享奖励！");
        }

        return Response::msg('您今天已经分享过啦，24小时以后再分享，会有惊喜！');

//        $list = (new \Live\Database\Gift())->getAll(10);
//
//        Response::data(['list' => $list]);
    }
}