<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

include BASE_DIR . 'Live/Third/qiniu/Auth.php';
include BASE_DIR . 'Live/Third/qiniu/Storage/UploadManager.php';

use Live\Response;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class Qiniu
{
    const AK = 'uk_JgveWYYcNXE730vQdHyRaAV86DplixzERLRy-';
    const SK = 'EHNf0jpUcLa8iVRO47aL178lF_zcPnsEwTE4LD-c';
    const HUB = 'kanhao';

    public $auth;

    public function __construct()
    {
        $this->auth = new Auth(self::AK, self::SK);
    }

    public function uploadCover($uid, $filename)
    {
        $bucket = 'live-cover';

        $token = $this->auth->uploadToken($bucket);

        $new_name = "{$uid}_" . \Swoolet\App::$ts;

        $uploadMgr = new UploadManager();

        list($ret, $err) = $uploadMgr->putFile($token, $new_name, $filename);

        if ($err)
            return Response::msg('上传失败', 1033);

        return $ret;
    }
}