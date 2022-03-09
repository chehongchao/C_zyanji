<?php

namespace app\GlobalServiceExchange\lib\pay;

use app\GlobalServiceExchange\lib\pay\hpjpay\Api;
use think\facade\Cache;

class HpjPay implements BasePay{
    public function getPaymentQrCode($orderInfo)
    {
        $hashKey = config("pay.HpjPay.app_secret");
        $orderInfo['hash'] = Api::generateHash($orderInfo, $hashKey);
        $url = config("pay.HpjPay.api_payment");
        $httpRequestRes = Api::curlPost($url, json_encode($orderInfo));

        if( $httpRequestRes["http_code"] === 200 ){
            $httpResponseDataArr = json_decode($httpRequestRes["response_data"],true);
            if( $httpResponseDataArr["errcode"]===0 ){

                $hash = Api::generateHash($httpResponseDataArr, $hashKey);
                if (isset($httpResponseDataArr['hash']) && $hash === $httpResponseDataArr['hash']) {
                    return $httpResponseDataArr["url"];
                }
            }
        }
        return false;
    }

    //单个订单查询
    public function tradeQuery($outTradeOrder)
    {
        #appid、appsecret
        $appId = config("pay.HpjPay.app_id");
        $appSecret = config("pay.HpjPay.app_secret");

        #组装http请求数据
        $requestData = [
            'appid' => $appId,
            'out_trade_order' => $outTradeOrder,
            'time' => time(),
            'nonce_str' => str_shuffle(time()),
        ];
        $requestData['hash'] = Api::generateHash($requestData, $appSecret);

        #获取订单查询地址
        $url = config("pay.HpjPay.api_order_query");;

        $httpRequestRes = Api::curlPost($url, http_build_query($requestData));

        if( $httpRequestRes["http_code"] === 200 ){
            $queryResultArr = json_decode($httpRequestRes["response_data"],true);

            return $queryResultArr["data"]["status"];
        }
    }
}