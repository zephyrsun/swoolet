<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;


use Live\Database\Balance;
use Live\Database\Fan;
use Live\Database\Follow;
use Live\Database\Income;
use Live\Database\UserLevel;
use Live\Response;

class Upload extends Basic
{
    public function cover($request)
    {
        var_dump($request->files);
    }
}