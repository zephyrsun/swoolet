<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;
use Live\Third\Qiniu;

class Upload extends Basic
{
    public $sdk;

    public function __construct()
    {
        $this->sdk = new Qiniu();
    }

    /**
     * 直播封面
     * @param $request
     */
    public function cover($request)
    {
        $bucket = 'static';

        $this->_upload($request, $bucket, 'cover', function ($uid, $img) use ($bucket) {
            $db = new \Live\Database\Live();
            $live = $db->getLive($uid);

            $ret = $db->updateLive($uid, [
                'cover' => $img
            ]);

            if ($ret && $live['cover']) {
                $this->sdk->delete($bucket, $live['cover']);
            }

            return $ret;
        });
    }

    /**
     * 个人主页封面
     * @param $request
     */
    public function userCover($request)
    {
        $bucket = 'static';

        $this->_upload($request, $bucket, 'user_cover', function ($uid, $img) use ($bucket) {
            $db = new \Live\Database\User();
            $user = $db->getShowInfo($uid, 'more');

            if (!$user['is_vip']) {
                $this->sdk->delete($bucket, $img);
                return Response::msg('此功能需要先开通会员！');
            }

            $ret = $db->updateUser($uid, [
                'cover' => $img
            ]);

            if ($ret && $user['cover']) {
                $this->sdk->delete($bucket, $user['cover']);
            }

            return $ret;
        });
    }


    public function avatar($request)
    {
        $bucket = 'static';

        $this->_upload($request, $bucket, 'avatar', function ($uid, $img) use ($bucket) {
            $db = new \Live\Database\User();
            $user = $db->getUser($uid);

            $ret = $db->updateUser($uid, [
                'avatar' => $img
            ]);

            if ($ret && $user['avatar']) {
                $this->sdk->delete($bucket, $user['avatar']);
            }

            return $ret;
        });
    }

    public function photo($request)
    {
        $bucket = 'static';

        $this->_upload($request, $bucket, 'photo', function ($uid, $img) use ($bucket) {

            $db = new \Live\Database\Album();

            return $db->add($uid, $img, '');
        });
    }

    private function _upload($request, $bucket, $prefix, $cb)
    {
        $data = parent::getValidator()->required('token')->getResult();
        if (!$data)
            return $data;

        if (!isset($request->files))
            return Response::msg('参数错误', 10035);

        $file = current($request->files);
        if (!$file || !$src = &$file['tmp_name'])
            return Response::msg('参数错误', 10036);

        $token_uid = $data['token_uid'];

        $arr = explode('.', $file['name']);
        $ext = end($arr);

        $name = "{$prefix}/{$token_uid}_" . \Swoolet\App::$ts . ".{$ext}";

        $img = $this->sdk->upload($bucket, $name, $src);
        if (!$img)
            return $img;

        if (!$ret = $cb($token_uid, $img))
            return $ret;

        return Response::data([
            'img' => $img
        ]);
    }
}