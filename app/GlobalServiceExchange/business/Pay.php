<?php

namespace app\GlobalServiceExchange\business;

use app\GlobalServiceExchange\lib\ClassArr;
use app\GlobalServiceExchange\lib\Show;
use think\facade\Cache;
use app\GlobalServiceExchange\lib\RedisOperate;

class Pay{

    public function index($params)
    {
        #获取订单号
        $outTradeNo = $params["out_trade_no"];

        #从redis中获取订单信息
        $hashMapName = config("good.order.redis.hash_map_name_order_info");
        $orderInfo = Cache::store('redis')->hGet($hashMapName,$outTradeNo);

        #优化：302到不存在此订单页面
        if( $orderInfo === false ){
            return redirect('/GlobalServiceExchange/Order/orderNotExist');
        }

        #获取订单数据
        $orderInfoArr = json_decode($orderInfo,true);
        $wxPayAddress = $orderInfoArr["qr_code"];
        return redirect($wxPayAddress);
    }
    
    //获取支付二维码
    public function getPaymentCode($params)
    {
        #sn、订单号
        $params = request()->param();
        $sn = $params["sn"];
        $outTradeNo = $params["out_trade_no"];

        #获取查询类型
        $queryType = RedisOperate::getQueryType($params);
        if($queryType===false){
            #优化：记录日志
            return Show::error("获取查询类型失败，请重试");
        }

        #判断支付二维码是否获取成功，成功二维码地址写入redis
        $qrCode = $this->getTradePrecreateResponse($queryType,$params);
        if($qrCode!==false){

            #获取redis中订单数据
            $hashMapName = config("good.order.redis.hash_map_name_order_info");
            $tempOrderInfo = Cache::store('redis')->hGet($hashMapName,$outTradeNo);
            $tempOrderInfoArr = json_decode($tempOrderInfo,true);

            #将sn、qr_code添加到订单数组中
            $tempOrderInfoArr["sn"] = $sn;
            $tempOrderInfoArr["qr_code"] = $qrCode;

            #将open_id添加到订单数组中
            $openId = $this->getOpenId();
            if($openId===false){
                return Show::error("订单创建失败，请重试");
            }
            $tempOrderInfoArr["open_id"] = $openId;

            #更新redis中订单数据
            $writeToRedisResult = Cache::store('redis')->hSet($hashMapName,$outTradeNo,json_encode($tempOrderInfoArr,JSON_UNESCAPED_UNICODE));
            if($writeToRedisResult === 0){
                return Show::success("订单创建成功，即将跳转到支付页面~");
            }
        }

        #优化：提示信息需要修改 ，是否跳转到查询页面
        return Show::error("订单创建失败，请重试");
    }

    //方法：获取open_id
    public function getOpenId()
    {
        #获取session中的temp_open_id
        $tempOpenId = session("temp_open_id");
        #未登录状态给出提示
        if($tempOpenId==null){
            return false;
        }
        #获取存储open_id的key的名称
        $gzhName = config("user.login.redis.gzh_name_abbreviation");
        $stringOpenIdInfoName = $gzhName.$tempOpenId;
        $openId = Cache::store('redis')->Get($stringOpenIdInfoName);
        return $openId === null ? false : $openId;
    }

    //方法：请求支付接口，获取二维码地址
    public function getTradePrecreateResponse($queryType,$params)
    {
        #从参数中获取订单号
        $outTradeNo = $params["out_trade_no"];

        #组装支付订单数据
        $title = config("good.query.".$queryType.".title");
        $price = config("good.query.".$queryType.".price");
        $orderInfo = [
            'version'   => '1.1',
            'lang'       => 'zh-cn',
            'plugins'   => "优化",//必须的，根据自己需要自定义插件ID，唯一的，匹配[a-zA-Z\d\-_]+
            'appid'     => config("pay.HpjPay.app_id"),
            'trade_order_id'=> $outTradeNo,
            'payment'   => 'wechat',
            'total_fee' => $price,
            'title'     => $title,
            'time'      => time(),
            'notify_url'=>  config("pay.HpjPay.callback_url_host")."/GlobalServiceExchange/pay/notify",
            'return_url'=> config("pay.HpjPay.callback_url_host").'/GlobalServiceExchange/Query/viewQueryResult.html?out_trade_no='.$outTradeNo,//必须的，支付成功后的跳转地址
            'callback_url'=>'http://www.xx.com/pay/checkout.html',//必须的，支付发起地址（未支付或支付失败，系统会会跳到这个地址让用户修改支付信息）
            'modal' => null, //可空，支付模式 ，可选值( full:返回完整的支付网页; qrcode:返回二维码; 空值:返回支付跳转链接)
            'nonce_str' => str_shuffle(time())
        ];

        #获取二维码url，并返回
        $classStats = ClassArr::payClassStat();
        $classObj = ClassArr::initClass("hpjpay", $classStats, [], true);
        return $classObj->getPaymentQrCode($orderInfo);
    }
}