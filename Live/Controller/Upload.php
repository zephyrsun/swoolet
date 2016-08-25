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

    public function cover($request)
    {
        $bucket = 'static';

        $this->_upload($request, $bucket, 'cover', function ($uid, $img) use ($bucket) {
            $db = new \Live\Database\Live();
            $live = $db->getLive($uid);
            if ($live['cover']) {
                $this->sdk->delete($bucket, $live['cover']);
            }

            $db->updateLive($uid, [
                'cover' => $img
            ]);
        });
    }

    public function avatar($request)
    {
        $bucket = 'static';

        $this->_upload($request, $bucket, 'avatar', function ($uid, $img) use ($bucket) {

            $db = new \Live\Database\User();
            $user = $db->getUser($uid);
            if ($user['avatar']) {
                $this->sdk->delete($bucket, $user['avatar']);
            }

            $db->updateUser($uid, [
                'avatar' => $img
            ]);
        });
    }

    public function photo($request)
    {
        $bucket = 'static';

        $this->_upload($request, $bucket, 'photo', function ($uid, $img) use ($bucket) {

            $db = new \Live\Database\Album();

            $db->add($uid, $img, '');
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

        $cb($token_uid, $img);

        return Response::data([
            'img' => $img
        ]);
    }
}