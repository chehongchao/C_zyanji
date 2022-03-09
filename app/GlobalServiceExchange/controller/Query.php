<?php

namespace app\GlobalServiceExchange\controller;

use app\GlobalServiceExchange\business\Query as QueryBusiness;
use app\GlobalServiceExchange\lib\Arr;
use app\GlobalServiceExchange\lib\Show;
use think\facade\Cache;

class Query{

    public function index()
    {
        $outTradeNo = request()->param()["out_trade_no"];
    }

    //获取GSX查询状态
    public function getGsxQueryStatus()
    {
        $outTradeNo = request()->param("out_trade_no");
        $hashMapName = config("good.order.redis.hash_map_name_order_info");
        $orderInfo = Cache::store('redis')->hGet($hashMapName,$outTradeNo);
        $orderInfoArr = json_decode($orderInfo,true);
        if(isset($orderInfoArr["query_result"])){
            return Show::success("GSX查询成功");
        }
        return Show::error("GSX查询未完成|未成功");
    }

    //查看GSX查询结果
    public function viewQueryResult()
    {
        $outTradeNo = request()->param("out_trade_no");
        $hashMapName = config("good.order.redis.hash_map_name_order_info");
        $isOrderExist = Cache::store('redis')->hExists($hashMapName,$outTradeNo);

        if($isOrderExist!==false) {
            return view(
                "",
                [
                    "title" => "GSX查询结果",
                    "out_trade_no" => $outTradeNo,
                ],
            );
        }

        return view(
            "order_not_exist",
            [
                "title" => "订单号不存在",
                "out_trade_no" => $outTradeNo,
            ],
        );
    }
    
    //GSX通用查询api
    public function unifiedQueryApi($outTradeNo)
    {
        #请求business层并返回数据
        $QueryBusinessObj = new QueryBusiness();
        return $QueryBusinessObj->unifiedQueryApi($outTradeNo);
    }

    //获取格式化后的GSX查询结果
    public function getQueryResult()
    {
        $outTradeNo = request()->param("out_trade_no");
        return json(Arr::getLayUiNeedDataFormat($outTradeNo));
    }
}