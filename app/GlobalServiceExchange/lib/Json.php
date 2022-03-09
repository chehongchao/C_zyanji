<?php

namespace app\GlobalServiceExchange\lib;

class Json{

    /*
     * 优化：三木运算符判断为true返回是，但非true可能存在多种情况(比如为空) 需要在后期数据量大的情况下进行分析后再改写
     * */

    public $dataArr, $queryStr, $queryArr;
    public function __construct($apiType,$data){

        $this->dataArr = $data;

        #判断查询类型(是否是验机报告)
        if( $apiType === "details-carrier" ){
            $this->queryStr = $this->dataArr["data"];
            $this->queryArr = explode("<br>",$this->queryStr);
            $this->queryArr = Arr::processDetailsCarrier($this->queryArr);
        }else{
            $this->queryArr = $this->dataArr["data"];
            $this->queryArr = Arr::processOther($this->queryArr);
        }

    }

    //苹果验机报告
    public function detailsCarrier()
    {
        return [
            "code" => $this->dataArr["code"],
            "query_result" => $this->queryArr,
        ];
    }

    //网络锁运营商购买地点
    public function carrier()
    {
        $this->queryArr["model"] === "" ? false : $data["型号"] = $this->queryArr["model"];
        $data["网络锁"] = str_replace("ed","",$this->queryArr["simlock"]);
        strtolower($data["网络锁"]) === "lock" ? $data["运营商"] = str_replace("ed","",$this->queryArr["carrier"]) : false;
        $data["购买地点"] = $this->queryArr["purchase"]["country"];

        return [
            "code" => $this->dataArr["code"],
            "query_result" => $data,
        ];
    }

    //激活锁ID黑白
    public function icloud()
    {
        $this->queryArr["model"] === "" ? false : $data["型号"] = $this->queryArr["model"];
        $data["激活锁"] = $this->queryArr["locked"] === true ? "开启" : "关闭";
        $data["ID黑白"] = str_replace("ed","",$this->queryArr["icloud"]);

        return [
            "code" => $this->dataArr["code"],
            "query_result" => $data,
        ];
    }

    //激活日期
    public function coveragePurchase()
    {
        $this->queryArr["model"] === "" ? false : $data["型号"] = $this->queryArr["model"];
        $this->queryArr["capacity"] === "" ? false : $data["容量"] = $this->queryArr["capacity"];
        $this->queryArr["color"] === "" ? false : $data["颜色"] = $this->queryArr["color"];
        $data["有效购买日期"] = $this->queryArr["activated"] === true ? "是" : "否";
        $data["激活日期"] = $this->queryArr["purchase"]["date"];

        return [
            "code" => $this->dataArr["code"],
            "query_result" => $data,
        ];
    }

    //官换+BS+借出设备+已更换+保修+生产日期+购买日期+激活锁+激活状态+预激活
    public function purchase()
    {
        $this->queryArr["model"] === "" ? false : $data["型号"] = $this->queryArr["model"];
        $this->queryArr["capacity"] === "" ? false : $data["容量"] = $this->queryArr["capacity"];
        $this->queryArr["color"] === "" ? false : $data["颜色"] = $this->queryArr["color"];
        $data["官换机"] = $this->queryArr["replacement"] === true ? "是" : "否";
        $data["BS机"] = $this->queryArr["brightstar"] === true ? "是" : "否";
        $data["借出设备"] = $this->queryArr["loaner"] === "N" || $this->queryArr["loaner"] === "" ? "否" : "是";
        $data["已更换产品的序列号"] = $this->queryArr["loaner"] === true ? "是" : "否";
        $data["保修状态"] = $this->queryArr["coverage"]["status"];
        $data["生产日期"] = $this->queryArr["manufacture"]["date"];
        $data["有效购买日期"] = $this->queryArr["activated"] === true ? "是" : "否";
        $data["购买日期"] = $this->queryArr["purchase"]["date"];
        $data["激活锁"] = strtolower($this->queryArr["fmi"]) === "on" ? "开启" : "关闭";
        $data["预激活"] = $this->queryArr["pre-activated"] === true ? "是" : "否";

        return [
            "code" => $this->dataArr["code"],
            "query_result" => $data,
        ];
    }
}