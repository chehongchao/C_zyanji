<?php

namespace app\GlobalServiceExchange\business;
use app\GlobalServiceExchange\model\Test as TestModel;
use app\GlobalServiceExchange\lib\RedisOperate;

class Test{
    public function test()
    {
        $openId = RedisOperate::getOpenId();
        return (new TestModel())->test($openId);
    }
}