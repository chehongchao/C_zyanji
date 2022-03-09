<?php

namespace app\GlobalServiceExchange\controller;

use think\facade\Cache;
use app\GlobalServiceExchange\lib\pay\HpjPay;


class Test
{
    public function test()
    {
        $res = (new \app\GlobalServiceExchange\business\Test())->test();
        var_dump($res);
        exit();
    }
}