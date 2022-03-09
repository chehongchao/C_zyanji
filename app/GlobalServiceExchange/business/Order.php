<?php

namespace app\GlobalServiceExchange\business;

use app\GlobalServiceExchange\lib\Show;
use app\GlobalServiceExchange\model\Order as OrderModel;
use app\GlobalServiceExchange\model\User as UserModel;
use think\facade\Cache;

class Order{
    public function processRedisOrder($outTradeNo="")
    {
        #如果传入的订单号为空
        if($outTradeNo==="") {
            #从redis延迟队列读取过期订单的订单号
            $sortedSetName = config("good.order.redis.sorted_set_name_order_status");
            $expireInOrderArr = Cache::store('redis')->zRangeByScore($sortedSetName, 0, time(), ["limit" => [0, 1]]);
            if(isset($expireInOrderArr[0])===false){
                $outTradeNo = false;
            }else{
                $outTradeNo = $expireInOrderArr[0];
            }
        }

        #判断是否有过期订单
        if( $outTradeNo !== false ){
            $hashMapNameOrderInfo = config("good.order.redis.hash_map_name_order_info");
            $orderInfo = Cache::store('redis')->hGet($hashMapNameOrderInfo, $outTradeNo); #优化：判断读取是否成功
            $orderInfoArr = json_decode($orderInfo, true);

            #GSX查询完成的订单，写入数据库
            if (isset($orderInfoArr["out_trade_no"]) && isset($orderInfoArr["query_result"])) {

                $openId = $orderInfoArr["open_id"];
                $userId = $this->getUserIdByOpenId($openId);
                if($userId===false){
                    #优化，记录日志或发送信息给开发者
                    return Show::error("获取用户id失败");
                }

                $orderInfoAllArr["sn"] = $orderInfoArr["sn"];
                $orderInfoAllArr["user_id"] = $userId;
                $orderInfoAllArr["out_trade_no"] = $orderInfoArr["out_trade_no"];
                $orderInfoAllArr["pay_time"] = $orderInfoArr["pay_time"];
                $orderInfoAllArr["query_type"] = $orderInfoArr["query_type"];
                $orderInfoAllArr["status"] = 0;
                $orderInfoAllArr["order_info"] = $orderInfo;
                $insertResult = (new OrderModel())->insertOrderInfo($orderInfoAllArr);
                if($insertResult===true){
                    $sortedSetName = config("good.order.redis.sorted_set_name_order_status");
                    $remResult = Cache::store('redis')->zRem($sortedSetName, $outTradeNo);
                    if($remResult===1){
                        return Show::success("订单信息写入数据库成功");
                    }
                    return Show::error("订单信息写入数据库成功、删除延迟队列订单号失败）"); #优化：记录日志|发送微信通知
                }
                return Show::error("订单信息写入数据库失败"); #优化：记录日志|发送微信通知
            }

            #超时的GSX查询未付款订单：删除延迟队列订单号，删除订单哈希表中订单
            Cache::store('redis')->zRem($sortedSetName, $outTradeNo);
            Cache::store('redis')->hDel($hashMapNameOrderInfo, $outTradeNo);
            return Show::success("超时订单释放成功：".$outTradeNo);
        }

        return Show::error("当前没有到期释放订单");

    }

    //方法：通过open_id获取用户id
    public function getUserIdByOpenId($openId)
    {
        $userId = (new UserModel())->getUserIdByOpenId($openId);
        if(isset($userId->id)){
            return $userId->id;
        }

        #数据库中不存在用户id，先写入后获取
        return (new UserModel())->insertUserInfo($openId);
    }

    /**
     * @desc 根据传入的查询条件获取当前用户的订单列表
     * @param $tempOpenId
     * @return array|false
     */
    public function getUserOrderInfoArrByStatus($tempOpenId,$op,$statusData)
    {
        #获取open_id
        $gzhName = config("user.login.redis.gzh_name_abbreviation");
        $stringOpenIdInfoName = $gzhName.$tempOpenId;
        $openId = Cache::store('redis')->Get($stringOpenIdInfoName);

        #从数据库获取user_id
        $userId = $this->getUserIdByOpenId($openId);
        if($userId===false){
            return false;
        }

        return (new OrderModel())->getUserOrderInfoArrByStatus($userId,$op,$statusData)->toArray();
    }

    public function refund($outTradeNo)
    {
        $orderModelObj = new OrderModel();
        $orderStatusObj = $orderModelObj->getOrderStatus($outTradeNo);
        if($orderStatusObj===null){
            #优化：记录日志
            return Show::error("退款申请失败，该订单不存在，请联系人工客服处理哦~");
        }

        //获取订单状态（只有状态为0，即待收货订单才可以申请退款）
        $orderStatus = $orderStatusObj->status;

        switch ($orderStatus){
            case 0:
                $status = 2;
                $setOrderStatusResult = $orderModelObj->setOrderStatus($outTradeNo,$status);
                if($setOrderStatusResult!==1){
                    #优化：记录日志
                    return Show::error("退款申请失败，请重试或联系人工客服处理哦~");
                }
                return Show::success("退款申请成功，我们将在二十四小时内为您处理，请耐心等待哟~");
            default:
                return Show::error("未知错误，请重试或联系人工客服处理哦~");
        }
    }
}