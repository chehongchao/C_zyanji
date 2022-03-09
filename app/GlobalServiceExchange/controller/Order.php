<?php

namespace app\GlobalServiceExchange\controller;

use app\GlobalServiceExchange\business\Order as OrderBusiness;
use app\GlobalServiceExchange\lib\Num;
use app\GlobalServiceExchange\lib\pay\HpjPay;
use app\GlobalServiceExchange\lib\Show;
use think\facade\Cache;

class Order{
    
    //生成订单号
    public function generateOutTradeNo()
    {
        $outTradeNo = Num::generateOutTradeNo();

        #订单号写入redis延迟队列
        $sortedSetName = config("good.order.redis.sorted_set_name_order_status");
        $score = time() + 3600;
        Cache::store('redis')->zAdd($sortedSetName,$score,$outTradeNo);

        $data = [
            "out_trade_no" => $outTradeNo,
        ];
        return Show::success("成功",$data);
    }

    //订单查询
    public function tradeQuery()
    {
        $outTradeNo = request()->param()["out_trade_no"];

        $payStatus = $this->getTradeQueryResult();

        switch ($payStatus)
        {
            case "OD":
                return Show::success("支付成功，开始查询~");
            case "WP":
                return Show::error("订单待支付~");
            case "CD":
                return Show::error("支付超时，请重新创建订单~");
            default:
                return Show::error("未知错误，请重试或联系人工客服处理~");
        }
    }

    /**
     * @desc 定时任务：删除redis中过期订单|（支付回调中）将已完成订单写入mysql
     * @return Object
     */
    public function processRedisOrder()
    {
        return (new OrderBusiness())->processRedisOrder();
    }

    //方法：获取订单查询结果
    public function getTradeQueryResult()
    {
        #获取支付类型、订单号
        $params = request()->param();
        $outTradeNo = $params["out_trade_no"];

        #获取支付二维码
        return (new HpjPay())->tradeQuery($outTradeNo);
    }
}