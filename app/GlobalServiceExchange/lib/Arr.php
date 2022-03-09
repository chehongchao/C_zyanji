<?php

namespace app\GlobalServiceExchange\lib;

use think\facade\Cache;

class Arr{

    //删除返回结果中的空值
    public static function wipeEmpty($data)
    {
        foreach ( $data as $key => $ip ){
            if( $ip === "" ){
                unset($data[$key]);
            }
        }
        return $data;
    }

    //对验机报告返回结果中的空值进行处理
    public static function processDetailsCarrier($data)
    {
        /*
         * 空值：设置提示
         * 购买日期1978：设置提示
         * */
        foreach ($data as $key => $value){
            if( preg_match("#.+：$#",$value) ){
                $data[$key] = $value."未找到相关信息";
            }elseif( $value === "购买日期：1978-04-01" ){
                $data[$key] = $value."(该设备为BS机，购买日期/激活日期统一为：1978-04-01，二手无保修，由苹果授权 Brightstar 销售。)";
            }
        }
        return $data;
    }

    //对其它返回结果中的空值进行处理
    public static function processOther($data)
    {
        foreach ($data as $key => $value){
            if( is_array($value) === true ){
                foreach ( $value as $secondKey => $secondValue ){
                    if( $secondValue === "" ){
                        $data[$key][$secondKey] = $secondValue."未找到相关信息";
                    }elseif( $secondValue === "1978-04-01" ){
                        $data[$key][$secondKey] = $secondValue."(该设备为BS机，购买日期/激活日期统一为：1978-04-01，二手无保修，由苹果授权 Brightstar 销售。)";
                    }
                }
            }else{
                if( $value === "" ){
                    $data[$key] = $value."未找到相关信息";
                }
            }
        }
        return $data;
    }

    //将GSX查询结果转换为layui动态表格需要的格式
    public static function getLayUiNeedDataFormat($outTradeNo)
    {
        #获取订单信息
        $hashMapName = config("good.order.redis.hash_map_name_order_info");
        $orderInfo = Cache::store('redis')->hGet($hashMapName,$outTradeNo);
        $orderInfoArr = json_decode($orderInfo,true);

        #获取序列号/IMEI、查询结果、查询类型
        $sn = $orderInfoArr["sn"];
        $queryResultArr = $orderInfoArr["query_result"]["query_result"];
        $queryType = $orderInfoArr["query_type"];

        #获取数组格式的查询结果
        $i = 0;
        foreach ($queryResultArr as $key => $value){
            if($i===0){
                $data[$i]["query_item"] = "序列号/IMEI";
                $data[$i]["query_result"] = $sn;
                $i = $i + 1;
            }

            if($queryType === "details_carrier"){
                $itemArr = explode("：",$value);

                if(count($itemArr)===2){
                    $data[$i]["query_item"] = $itemArr[0];
                    $data[$i]["query_result"] = $itemArr[1];
                    $i = $i + 1;
                }
            }else{
                $data[$i]["query_item"] = $key;
                $data[$i]["query_result"] = $value;
                $i = $i + 1;
            }
        }

        return [
            "code" => 0,
            "msg" => "",
            "count" => count($data),
            "data" => $data,
        ];
    }
}