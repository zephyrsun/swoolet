<?php
/**
 * 沙塔CDN
 */

namespace Live\Controller;

use Live\Response;

class Shata extends Basic
{
    public function callback($request)
    {
        (new \Live\Database\Log())->add($request, '');

        $data = $request->get;
        if ($data['action'] != 'stop')
            return Response::msg('');

        (new \Live\Lib\Live(new \Live\Third\Shata()))->stop($data['id']);

        return Response::msg('');
    }
}