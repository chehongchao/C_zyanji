<?php

namespace app\GlobalServiceExchange\controller;

use app\GlobalServiceExchange\lib\RedisOperate;
use think\helper\Str;

class Good{

    //商品列表
    public function index()
    {
        return view(
            "",
            [
                "title" => "GSX查询",
            ],
        );
    }

    //激活日期 查询
    public function coveragePurchase()
    {
        return $this->processRequest();
    }

    //生产日期、购买日期、激活状态、激活锁、保修、官换、BS、已更换、借出设备 查询
    public function purchase()
    {
        return $this->processRequest();
    }

    //验机报告 查询
    public function detailsCarrier()
    {
        return $this->processRequest();
    }

    //激活锁、ID黑白 查询
    public function icloud()
    {
        return $this->processRequest();
    }

    //网络锁、运营商、购买地点 查询
    public function carrier()
    {
        return $this->processRequest();
    }

    public function processRequest()
    {
        #获取请求类型
        $method = strtolower(request()->method());

        #获取当前方法名（驼峰式、下划线式）
        $functionName = request()->action();
        $queryType = Str::snake($functionName);

        #如果是post请求，将当前方法名（驼峰式、下划线式写入redis）
        if( $method === "post" ){
            return RedisOperate::recordOutTradeNo();
        }

        #如果是get请求，返回html页面
        if( $method === "get" ){
            return view(
                "unified_query_page",
                [
                    "title" => config("good.query.".$queryType.".title"),
                    "function_name" => $functionName,
                    "query_type" => $queryType,
                ]
            );
        }

        return "";
    }

    //获取查询结果示例图片的url
    public function getQueryResultExampleImgUrl()
    {
        #接收用户参数
        $queryType = request()->param("query_type");

        #参数校验？

        #获取查询结果示例图片的url
        $queryResultExampleImgUrl = config("good.query_result_example_img_url.".$queryType);

        if(gettype($queryResultExampleImgUrl)==="string"){
            $queryResultExampleImgUrl = [$queryResultExampleImgUrl];
        }

        foreach ($queryResultExampleImgUrl as $index => $value){
            $data[$index]["alt"] = "";
            $data[$index]["pid"] = $index;
            $data[$index]["src"] = $value;
            $data[$index]["thumb"] = $value;
        }

        $imgInfoArr = [
            "status" => 1,
            "msg" => "暂无",
            "title" => "JSON请求的相册",
            "id" => 1,
            "start" => 0,
            "data" => $data,
        ];
        return json($imgInfoArr);
    }
}