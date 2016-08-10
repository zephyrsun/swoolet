<?php

/**
 * Cookie
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Swoolet\Lib;

use Swoolet\App;

class Crypt
{
    public $option = [
        'algo' => \MCRYPT_RIJNDAEL_256,
        'mode' => \MCRYPT_MODE_CBC,
        'key' => 'SWOOLET_WARNING_PLEASE_CHANGE_KEY',
        //'secret' => '',
    ];

    public $secret;

    public function __construct($secret)
    {
        $this->option = App::getConfig('crypt') + $this->option;
        $this->secret = $secret;
    }

    public function init()
    {
        $cfg = $this->option;

        $cipher = \mcrypt_module_open($cfg['algo'], '', $cfg['mode'], '');

        $hash_key = hash_hmac('sha1', $cfg['key'], $this->secret);

        $key = substr($hash_key, 0, \mcrypt_enc_get_key_size($cipher));

        $iv = substr(pack('h*', $key . $hash_key), 0, \mcrypt_enc_get_iv_size($cipher));

        mcrypt_generic_init($cipher, $key, $iv);

        return $cipher;
    }

    public function encrypt($str)
    {
        if ($str) {
            $cipher = $this->init();
            $str = base64_encode(mcrypt_generic($cipher, $str));
            mcrypt_generic_deinit($cipher);
        }

        return $str;
    }

    public function decrypt($str)
    {
        if ($str && $str = base64_decode($str)) {
            $cipher = $this->init();
            $str = mdecrypt_generic($cipher, $str); //$str = rtrim($str, "\0");
            mcrypt_generic_deinit($cipher);
        }

        return $str;
    }
}