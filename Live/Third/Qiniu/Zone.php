<?php
namespace Qiniu;

final class Zone
{
    public $upHost;
    public $upHostBackup;

    public function __construct($upHost, $upHostBackup)
    {
        $this->upHost = $upHost;
        $this->upHostBackup = $upHostBackup;
    }

    public static function zone0()
    {
        return new self('http://up.Qiniu.com', 'http://upload.Qiniu.com');
    }

    public static function zone1()
    {
        return new self('http://up-z1.Qiniu.com', 'http://upload-z1.Qiniu.com');
    }
}
