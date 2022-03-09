<?php

namespace app\GlobalServiceExchange\model;

use think\Model;

class Order extends Model{

    /**
     * @desc 插入订单信息
     * @param $orderInfoAllArr
     * @return bool
     */
    public function insertOrderInfo($orderInfoAllArr)
    {
        return $this->save($orderInfoAllArr);
    }

    /**
     * @param $outTradeNo
     * @desc 获取订单状态
     */
    public function getOrderStatus($outTradeNo)
    {
        return $this->where("out_trade_no",$outTradeNo)->field("status")->find();
    }


    /**
     * @param $outTradeNo
     * @desc 设置订单状态
     */
    public function setOrderStatus($outTradeNo,$status)
    {

        $where = [
            "out_trade_no" => $outTradeNo,
        ];

        $data = [
            "status" => $status,
        ];

        return $this->where($where)->save($data);
    }

    /**
     * @param $userId
     * @param $op
     * @param $statusData
     * @param $num
     * @return \think\Paginator
     */
    public function getUserOrderInfoArrByStatus($userId,$op,$statusData,$num = 4)
    {
        #排序规则
        $order = [
            "id" => "desc",
            "pay_time" => "desc",
        ];

        $where = [
            "user_id" => $userId,
        ];

        #指定要查询的列
        $field = "out_trade_no,pay_time,query_type,status,sn";

        return $this->order($order)->field($field)->where($where)->where("status",$op,$statusData)->paginate($num);
    }
}