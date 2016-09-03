<?php
/**
 * Created by PhpStorm.
 * User: sunzhenghua
 * Date: 16/8/17
 * Time: 下午7:31
 */

namespace Live\Lib;


class Utility
{
    static public function getZodiac($month, $day)
    {
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31)
            return '';

        $zodiac = [
            [20 => '水瓶座'],
            [19 => '双鱼座'],
            [21 => '白羊座'],
            [20 => '金牛座'],
            [21 => '双子座'],
            [22 => '巨蟹座'],
            [23 => '狮子座'],
            [23 => '处女座'],
            [23 => '天秤座'],
            [24 => '天蝎座'],
            [22 => '射手座'],
            [22 => '摩羯座']
        ];

        list($start, $name) = each($zodiac[$month - 1]);

        if ($day < $start)
            list($start, $name) = each($zodiac[($month - 2 < 0) ? $month = 11 : $month -= 2]);

        return $name;
    }

    static public function generateCity($city)
    {
        if ($city)
            return $city;

        $a1 = ['未知', '黑暗', '玲珑', '遗忘', '灵魂'];
        $a2 = ['世界', '深渊', '沙漠', '峡谷', '宇宙'];

        return array_rand($a1) . array_rand($a2);
    }
}