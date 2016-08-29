<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;

class NginxRtmp extends Basic
{
    public function publish($request)
    {
        var_dump($request, $request->rawContent());
    }

    public function done($request)
    {
        var_dump($request, $request->rawContent());
    }

    public function recordDone($request)
    {
        var_dump($request, $request->rawContent());
    }
}

