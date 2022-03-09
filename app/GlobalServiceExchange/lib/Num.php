<?php

namespace app\GlobalServiceExchange\lib;
use DateTime;

class Num{

    //生成唯一订单号（优化：判断订单号是否存在）
    public static function generateOutTradeNo()
    {
        $date = new DateTime();
        $nowTime = $date->format("YmdGisu");
        $randomLetter = chr(rand(65,90));
        return $nowTime.$randomLetter.mt_rand(0,99999999999);
    }
}