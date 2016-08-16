<?php

/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/1
 * Time: 上午9:50
 */

namespace Live\Third;

include BASE_DIR . 'Live/Third/Qiniu/Auth.php';
include BASE_DIR . 'Live/Third/Qiniu/Zone.php';
include BASE_DIR . 'Live/Third/Qiniu/Config.php';
include BASE_DIR . 'Live/Third/Qiniu/functions.php';
include BASE_DIR . 'Live/Third/Qiniu/Storage/UploadManager.php';
include BASE_DIR . 'Live/Third/Qiniu/Storage/FormUploader.php';
include BASE_DIR . 'Live/Third/Qiniu/Http/Client.php';
include BASE_DIR . 'Live/Third/Qiniu/Http/Request.php';
include BASE_DIR . 'Live/Third/Qiniu/Http/Response.php';
include BASE_DIR . 'Live/Third/Qiniu/Http/Error.php';


use Live\Response;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class Qiniu
{
    const AK = 'uk_JgveWYYcNXE730vQdHyRaAV86DplixzERLRy-';
    const SK = 'EHNf0jpUcLa8iVRO47aL178lF_zcPnsEwTE4LD-c';
    const HUB = 'kanhao';

    public $auth;

    public $domain = [
        'static' => 'http://obzd2fzvx.bkt.clouddn.com/',
    ];

    public function __construct()
    {
        $this->auth = new Auth(self::AK, self::SK);
    }

    public function upload($bucket, $filename, $key)
    {
        $token = $this->auth->uploadToken($bucket);

        $uploadMgr = new UploadManager();

        list($ret, $err) = $uploadMgr->putFile($token, $key, $filename);

        if ($err)
            return Response::msg('上传失败', 1033);

        return $this->domain[$bucket] . $key;
    }
}