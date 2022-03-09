<?php

namespace app\GlobalServiceExchange\validate;

use think\facade\Cache;
use think\Validate;

class Query extends Validate{


    protected $rule = [
        "out_trade_no" => "require|isOrderExist",
    ];


    protected $message = [
        "out_trade_no.require" => "订单号不能为空",
        "out_trade_no.isOrderExist" => "订单号不存在",
    ];

    protected $scene = [
        'get_payment_code'  =>  ['out_trade_no'],
    ];

    //判断订单号是否存在
    protected function isOrderExist($value)
    {
        $hashMapName = config("good.order.redis.hash_map_name_order_info");

        return Cache::store('redis')->hExists($hashMapName,$value);
    }
}