<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/3
 * Time: 下午6:08
 */

namespace Live\Controller;

use Live\Response;
use Swoolet\Lib\CURL;

class Map extends Basic
{
    public function getCity()
    {
        $data = parent::getValidator()->required('token')->required('location')->getResult();
        if (!$data)
            return $data;

        $location = $data['location'];

        if ($location) {
            $curl = new CURL();
            $ret = $curl->get("http://api.map.baidu.com/geocoder/v2/?ak=Zf1CgyTr4YiC2RWsE39T21jj&location={$location}&output=json");

            $ret = json_decode($ret, true);
            if ($ret['status'] == 0) {
                $city = $ret['result']['addressComponent']['city'];
                return Response::data(['city' => $city]);
            }
        }

        return Response::msg('服务异常', 1036);
    }
}