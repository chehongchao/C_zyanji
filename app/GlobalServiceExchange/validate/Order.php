<?php

namespace app\GlobalServiceExchange\validate;

use think\facade\Cache;
use think\Validate;

class Order extends Validate{


    protected $rule = [
        "sn" => "require",
        "query_type" => "require|isQueryTypeExist",
        "out_trade_no" => "require|isOrderExist",
    ];


    protected $message = [
        "sn.require" => "IMEI/序列号不能为空",

        "query_type.require" => "查询类型不能为空",
        "query_type.isQueryTypeExist" => "查询类型不正确",

        "out_trade_no.require" => "订单号不能为空",
        "out_trade_no.isOrderExist" => "订单号不存在",
    ];


    protected $scene = [
        'get_payment_code'  =>  ['sn','out_trade_no'],
        'activate_date_query'  =>  ['out_trade_no'],
        'unified_query_api'  =>  ['out_trade_no'],
    ];


    #判断订单号是否存在
    protected function isOrderExist($value)
    {
        $hashMapName = config("good.order.redis.hash_map_name_order_info");

        return Cache::store('redis')->hExists($hashMapName,$value);
    }

    #判断传入的查询类型是否存在
    public function isQueryTypeExist($value)
    {
        #获取所有的查询类型
        $queryInfoArr = config("good.query");
        $queryTypeArr = array_map("array_shift",$queryInfoArr);

        #判断查询类型是否存在
        if(in_array($value,$queryTypeArr)){
            return true;
        }

        return false;
    }
}