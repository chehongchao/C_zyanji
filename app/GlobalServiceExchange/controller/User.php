<?php

namespace app\GlobalServiceExchange\controller;

use app\GlobalServiceExchange\business\Order as OrderBusiness;
use app\GlobalServiceExchange\business\User as UserBusiness;
use app\GlobalServiceExchange\lib\RedisOperate;
use app\GlobalServiceExchange\lib\Show;

class User{

    //用户登录页面
    public function login()
    {
        $openId = request()->param("open_id"); #优化：判断open_id是否真实
        $openId = trim($openId);

        #优化：写到validate里面
        #如果传入的open_id为空
        if($openId===""){
            $gzhName = config("user.login.gzh.gzh_name");
            $keyword = config("user.login.gzh.keyword");

            $message = '<div class="layui-font-14" style="margin-bottom: 0.6rem;">登录信息已过期，请先关注公众号：<span style="color:#FF5722;">'.$gzhName.'</span> ，后台回复关键字 <span style="color:#FF5722;">'.$keyword.'</span> ，即可获取 <span style="color:#01AAED;">登录链接哦~</span> 哦~<div style="margin-top: 0.3rem;"><img src="https://tva1.sinaimg.cn/large/006Bi2D5gy1gzz33zbvuwj31ne0lotg3.jpg" style="width: 100%;" /></div></div>';
            return view(
                "",
                [
                    "title" => "请登录",
                    "login_status" => 0,
                    "message" => $message,
                ],
            );
        }

        #优化：当前open_id md5值与session中temp_open_id值不相同的情况？

        #调用business层进行登录
        $loginStatus = (new UserBusiness())->login($openId);
        $loginStatus = intval($loginStatus);
        return view(
            "",
            [
                "title" => "登录",
                "login_status" => $loginStatus,
            ],
        );

    }

    //用户设置
    public function account()
    {
        $tempOpenId = session("temp_open_id");
        $tempOpenId = substr($tempOpenId,0,15);
        $tempOpenId = strtoupper($tempOpenId);

        $userInfo = (new UserBusiness())->getUserInfo();

        return view(
            "",
            [
                "title" => "我的账户",
                "temp_open_id" => $tempOpenId,
                "user_info" => $userInfo,
            ],
        );
    }

    /**
     * @desc 更新用户的收款人姓名、支付宝/银行卡账号
     * @return void
     */
    public function updateUserInfo()
    {
        #接收客户端参数
        $userInfo = request()->param();

        #优化参数校验

        return (new UserBusiness())->updateUserInfo($userInfo);
    }

    /**
     * @desc 获取当前用户的所有订单
     * @return \think\response\Json|\think\response\View
     */
    public function getUserAllOrderInfo()
    {
        $op = "<=";
        $statusData = 5;
        return $this->processRequest($op,$statusData);
    }

    /**
     * @desc 获取当前用户的已完成订单
     * @return \think\response\Json|\think\response\View
     */
    public function getUserCompleteOrderInfo()
    {
        $op = "in";
        $statusData = [0,1];
        return $this->processRequest($op,$statusData);
    }

    /**
     * @desc 获取当前用户退款的订单
     * @return \think\response\Json|\think\response\View
     */
    public function getUserRefundOrderInfo()
    {
        $op = "in";
        $statusData = [2,3,4,5];
        return $this->processRequest($op,$statusData);
    }
    
    //方法：处理用户请求并返回结果
    public function processRequest($op,$statusData)
    {
        #获取temp_open_id
        $tempOpenId = session("temp_open_id");

        #优化：参数验证？真的需要？中间件不是验证过了吗

        #调用business层获取分页数据
        $orderInfoArr = (new OrderBusiness())->getUserOrderInfoArrByStatus($tempOpenId,$op,$statusData);

        if($orderInfoArr===false){
            #优化：指定一个通用错误模板，返回view格式
            return Show::error("暂无订单信息，请先下单后再来查看哦~");
        }

        #获取所有查询类型及其价格
        $queryInfoArr = config("good.query");
        foreach ($queryInfoArr as $queryType => $value){
            $queryTypePrice[$queryType] = $value["price"];
        }

        #获取查看查询结果页url
        $viewQueryResultUrl = config("common.host")."/GlobalServiceExchange/Query/viewQueryResult?out_trade_no=";
        $customerServiceUrl = config("common.host")."/GlobalServiceExchange/help/customerService.html";

        $displayMenu = $this->getDisplayMenu();
        return view(
            "get_user_order_info",
            [
                "title" => "订单列表",
                "order_info_arr" => $orderInfoArr,
                "query_type_price" => $queryTypePrice,
                "view_query_result_url" => $viewQueryResultUrl,
                "customer_service_url" => $customerServiceUrl,
                "display_menu" => $displayMenu,
            ],
        );
    }

    //方法的方法：获取要显示菜单中的哪一个
    public function getDisplayMenu()
    {
        #获取当前方法名
        $functionName = request()->action();

        switch ($functionName)
        {
            case "getUserAllOrderInfo":
                return ["menu_display_all"=>"layui-this"];
            case "getUserCompleteOrderInfo":
                return ["menu_display_complete"=>"layui-this"];
            case "getUserRefundOrderInfo":
                return ["menu_display_refund"=>"layui-this"];
        }
    }
    
    /**
     * @desc 用户申请退款接口
     */
    public function refund()
    {
        $outTradeNo = request()->param("out_trade_no");
        return (new OrderBusiness())->refund($outTradeNo);
    }

    public function checkPayeeInfo()
    {
        $openId = RedisOperate::getOpenId();

        return (new UserBusiness())->checkPayeeInfo($openId);
    }
}