<?php

namespace app\GlobalServiceExchange\business;

use app\GlobalServiceExchange\lib\ClassArr;
use app\GlobalServiceExchange\lib\Json;
use app\GlobalServiceExchange\lib\Show;
use think\facade\Cache;

class Query{
    //GSX通用查询
    public function unifiedQueryApi($outTradeNo)
    {
        #获取存储原始查询结果的哈希表名称
        $hashMapNameOriginalQueryResult = config("good.order.redis.hash_map_name_original_query_result");

        #获取redis中订单数据
        $hashMapNameOrderInfo = config("good.order.redis.hash_map_name_order_info");
        $orderInfo = Cache::store('redis')->hGet($hashMapNameOrderInfo,$outTradeNo); #优化：判断读取是否成功

        $orderInfoArr = json_decode($orderInfo,true);

        if( isset($orderInfoArr["query_status"]) ){
            if( isset($orderInfoArr["query_result"]) ){
                return Show::success("查询成功~");
            }
            #优化：记录日志
            return Show::error("查询失败，请联系客服处理~");
        }

        #设置支付状态为1，避免重复查询
        $orderInfoArr["query_status"] = 1;
        $setQueryStatus = Cache::store('redis')->hSet($hashMapNameOrderInfo,$outTradeNo,json_encode($orderInfoArr));
        if($setQueryStatus!==0){
            Show::error("查询失败，请联系客服处理~~~");
        }

        #获取api类型
        $queryType = $orderInfoArr["query_type"];
        $apiType = config("good.query.".$queryType.".api_type");

        #获取查询结果
        $httpRequestRes = $this->curlGetQueryResult($orderInfoArr);

        if( $httpRequestRes["http_code"] === 200 ){

            #记录原始查询结果到redis
            Cache::store('redis')->hSet($hashMapNameOriginalQueryResult,$outTradeNo,$httpRequestRes["response_data"]);

            #将原始查询结果转换为标准格式
            $queryResultArr = json_decode($httpRequestRes["response_data"],true);

            #如果GSX查询返回结果状态码不为0，或者code不存在，不处理返回结果，直接返回对应格式的数据
            if( isset($queryResultArr["code"]) === false ){
                $queryItem = config("good.query.".$queryType.".title");
                $queryResultArrFormat = json_decode('{"code":500,"query_result":{"'.$queryItem.'": "出现未知错误，请联系人工客服处理哦~"}}',true);
            }elseif( $queryResultArr["code"] !== 0 ){
                $queryItem = config("good.query.".$queryType.".title");
                $queryResultArrFormat = json_decode('{"code":'.$queryResultArr["code"].',"query_result":{"'.$queryItem.'": "'.$queryResultArr["message"].'"}}',true);
            }
            else{
                $queryResultArrFormat = $this->processData($apiType,$queryResultArr);
            }

            $orderInfoArr["query_result"] = $queryResultArrFormat;

            #将转换为标准格式的查询结果追加到哈希表中
            $res = Cache::store('redis')->hSet($hashMapNameOrderInfo,$outTradeNo,json_encode($orderInfoArr,JSON_UNESCAPED_UNICODE));

            #hget返回值为0表示更新成功
            if($res===0){
                return Show::success("查询成功");
            }
            return Show::error("查询失败~");

        }

        return false;

    }

    //方法：返回数据处理(格式化)
    public function processData($apiType,$data)
    {
        $jsonObj = new Json($apiType,$data);

        switch ($apiType) {
            case "details-carrier":
                return $jsonObj->detailsCarrier();
            case "carrier":
                return $jsonObj->carrier();
            case "icloud":
                return $jsonObj->icloud();
            case "coverage-purchase":
                return $jsonObj->coveragePurchase();
            case "purchase":
                return $jsonObj->purchase();
        }
    }


    //方法：http请求
    public function curlGetQueryResult($data)
    {
        #优化
        $secretKey = config("query.secret_key");

        $sn = $data["sn"];
        $sn = trim($sn);

        $queryType = $data["query_type"];
        $queryApiUrl = config("good.query.".$queryType.".query_api_url")."&sn=".$sn;;

        $ch = curl_init($queryApiUrl);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array("key: ".$secretKey));
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_DNS_CACHE_TIMEOUT,28800);
        curl_setopt($ch,CURLOPT_TIMEOUT,300);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
        $responseData = curl_exec($ch);
        $responseError = curl_error($ch);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        return [
            "http_code" => $httpCode,
            "response_data" => $responseData,
            "response_error" => $responseError,
        ];
    }
}