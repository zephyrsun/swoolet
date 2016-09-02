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
include BASE_DIR . 'Live/Third/Qiniu/Storage/BucketManager.php';
include BASE_DIR . 'Live/Third/Qiniu/Http/Client.php';
include BASE_DIR . 'Live/Third/Qiniu/Http/Request.php';
include BASE_DIR . 'Live/Third/Qiniu/Http/Response.php';
include BASE_DIR . 'Live/Third/Qiniu/Http/Error.php';


use Live\Response;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Swoolet\App;

class Qiniu
{
    public $auth;

    public $domain = [
        'static' => 'http://static.camhow.com.cn/',
    ];

    public function __construct()
    {
        $cfg = App::getConfig('qiniu');

        $this->auth = new Auth($cfg['key'], $cfg['secret']);
    }

    public function upload($bucket, $key, $src)
    {
        $token = $this->auth->uploadToken($bucket);

        $manger = new UploadManager();

        list($ret, $err) = $manger->putFile($token, $key, $src);

        if ($err)
            return Response::msg('上传失败', 1033);

        return $this->domain[$bucket] . $key;
    }

    public function delete($bucket, $key)
    {
        $manager = new BucketManager($this->auth);

        $key = str_replace($this->domain['static'], '', $key);

        $err = $manager->delete($bucket, $key);

        return !$err;
    }
}