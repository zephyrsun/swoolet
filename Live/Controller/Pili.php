<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;

class Pili extends Basic
{
    public function callback($request)
    {
        if ($raw = $request->rawContent())
            return Response::msg('');

        //$raw ='{"message":"streamStatus","updatedAt":"2016-08-18T12:48:03.052020748+08:00","data":{"id":"z1.kanhao.test-1","url":"rtmp://127.0.0.1/kanhao/test-1?key=e29d8b322fdf8020\u0026sourceConnId=9Qdx2V_eRG-TUe6D\u0026node=vdntestvery55\u0026remote=116.226.189.83%3A39700","status":"disconnected"}}';

        $raw = \json_decode($raw, true);
        if (!($data = &$raw['data']) || $data['status'] != 'disconnected')
            return Response::msg('');

        list($_, $uid) = explode('-', $data['id'], 2);

        (new \Live\Lib\Live(new \Live\Third\Pili()))->stop($uid);

        return Response::msg('');
    }
}