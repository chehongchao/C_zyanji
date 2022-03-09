<?php

namespace app\GlobalServiceExchange\validate;

use think\facade\Cache;
use think\Validate;

class Pay extends Validate{

    protected $rule = [
        "sn" => "require|isCorrectFormat",
        "out_trade_no" => "require|isOrderExist",
        "pay_type" => "isCorrectPayType",
    ];


    protected $message = [
        "sn.require" => "IMEI/序列号不能为空",
        "sn.isCorrectFormat" => "序列号/IMEI格式不正确",

        "out_trade_no.require" => "订单号不能为空",
        "out_trade_no.isOrderExist" => "订单号不存在",

        "pay_type.isCorrectPayType" => "支付类型错误~",
    ];


    protected $scene = [
        'get_payment_code'  =>  ['sn','out_trade_no'],
        'get_index_page'  =>  ['pay_type','out_trade_no'],
    ];


    //判断序列号/IMEI格式是否正确
    public function isCorrectFormat($value)
    {
        $sn = strtoupper(trim($value));

        if( preg_match("/^[0-9]{6,30}$/",$sn) || preg_match("/^[0-9a-zA-Z]{6,30}$/",$sn) ){
            return true;
        }

        return false;
    }


    #判断订单号是否存在
    protected function isOrderExist($value)
    {
        $hashMapName = config("good.order.redis.hash_map_name_order_info");

        return Cache::store('redis')->hExists($hashMapName,$value);
    }

    //判断客户端传入的支付类型是否正确
    public function isCorrectPayType($value)
    {
        $payTypeArr = ["alif2fpay","hpjpay"];

        if(in_array($value,$payTypeArr)){
            return true;
        }
        return false;
    }
}