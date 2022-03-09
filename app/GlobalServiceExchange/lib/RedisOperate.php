<?php

namespace app\GlobalServiceExchange\lib;

use think\facade\Cache;
use think\helper\Str;

class RedisOperate{
    //获取查询类型
    public static function getQueryType($data)
    {
        if(gettype($data)==="array"){
            $outTradeNo = $data["out_trade_no"];
        }else{
            $outTradeNo = $data;
        }

        $hashMapName = config("good.order.redis.hash_map_name_order_info");
        $isExist = Cache::store('redis')->hExists($hashMapName,$outTradeNo);
        if($isExist===true){
            $orderInfo = Cache::store('redis')->hGet($hashMapName,$outTradeNo);
            $orderInfoArr = json_decode($orderInfo,true);
            return $orderInfoArr["query_type"];
        }else{
            return false;
        }
    }

    //记录订单号
    public static function recordOutTradeNo()
    {
        #获取客户端参数、订单号
        $params = request()->param();
        $outTradeNo = $params["out_trade_no"];

        #获取当前方法名（驼峰式、下划线式）
        $functionName = request()->action();
        $functionNameLowercaseUnderscore = Str::snake($functionName);

        #将订单信息写入数组
        $orderInfo["out_trade_no"] = $outTradeNo;
        $orderInfo["query_type"] = $functionNameLowercaseUnderscore;
        $orderInfo["function_name"] = $functionName;

        #将订单号及订单信息写入redis（订单号不存在的情况下，否则返回错误信息）
        $hashMapName = config("good.order.redis.hash_map_name_order_info");
        $isExist = Cache::store('redis')->hExists($hashMapName,$outTradeNo);
        if($isExist===false){
            Cache::store('redis')->hSet($hashMapName,$outTradeNo,json_encode($orderInfo));
            return Show::success("订单号记录成功~");
        }

        return Show::error("订单号已存在，需要重新生成~");
    }

    //获取open_id
    public static function getOpenId()
    {
        $tempOpenId = session("temp_open_id");

        #根据open_id生成temp_user_id
        $gzhName = config("user.login.redis.gzh_name_abbreviation");

        #从redis中获取openId
        $stringOpenIdInfoName = $gzhName.$tempOpenId;
        return Cache::store('redis')->Get($stringOpenIdInfoName);
    }
}