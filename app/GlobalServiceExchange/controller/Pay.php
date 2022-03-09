<?php

namespace app\GlobalServiceExchange\controller;

use app\GlobalServiceExchange\business\Pay as PayBusiness;
use app\GlobalServiceExchange\business\Order as OrderBusiness;
use app\GlobalServiceExchange\lib\Show;
use app\GlobalServiceExchange\validate\Pay as PayValidate;
use app\GlobalServiceExchange\lib\RedisOperate;
use app\GlobalServiceExchange\lib\pay\HpjPay;
use think\facade\Cache;

class Pay{

    public function index()
    {
        #获取客户端参数
        $params = request()->param();

        #参数验证
        $validateObj = new PayValidate();
        $checkResult = $validateObj->scene("get_index_page")->check($params);
        if( $checkResult === false ){
            #优化：记录日志，不把原错误信息显示给用户
            return Show::error($validateObj->getError());
        }

        #调用business层并返回结果给客户端
        return (new PayBusiness())->index($params);

    }
    
    //获取支付二维码信息
    public function getPaymentCode()
    {
        #接收参数
        $params = request()->param();

        #获取查询类型
        $queryType = RedisOperate::getQueryType($params);
        if($queryType===false){
            #优化：记录日志
            return Show::error("获取查询类型失败，请重试");
        }

        #参数验证
        $validateObj = new PayValidate();
        $checkResult = $validateObj->scene($queryType)->check($params);
        if( $checkResult === false ){
            #优化：记录日志，不把原错误信息显示给用户
            return Show::error($validateObj->getError());
        }

        #调用business层、返回结果
        $PayBusinessObj = new PayBusiness();
        return $PayBusinessObj->getPaymentCode($params);
    }

    //付款成功回调通知
    public function notify()
    {
        $params = request()->param();
        $outTradeNo = $params["trade_order_id"];

        $isPaySuccess = (new HpjPay())->tradeQuery($outTradeNo);
        if($isPaySuccess==="OD"){
            echo "success";
            (new Query())->unifiedQueryApi($outTradeNo);

            #将支付时间写入redis
            $hashMapNameOrderInfo = config("good.order.redis.hash_map_name_order_info");
            $orderInfo = Cache::store('redis')->hGet($hashMapNameOrderInfo,$outTradeNo); #优化：判断读取是否成功
            $orderInfoArr = json_decode($orderInfo,true);
            $orderInfoArr["pay_time"] = time();
            Cache::store('redis')->hSet($hashMapNameOrderInfo,$outTradeNo,json_encode($orderInfoArr,JSON_UNESCAPED_UNICODE));

            #将redis中的订单信息写入数据库
            (new OrderBusiness())->processRedisOrder($outTradeNo);

        }
    }
}