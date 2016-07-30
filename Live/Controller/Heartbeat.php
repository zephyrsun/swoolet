<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/7/28
 * Time: 下午5:51
 */

namespace Live\Controller;

use \Swoolet\App;
use \Live\Response;

class Heartbeat extends Basic
{
    public function index()
    {
        App::response('');
    }
}