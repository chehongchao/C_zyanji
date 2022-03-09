<?php

namespace app\GlobalServiceExchange\business;

use app\GlobalServiceExchange\lib\RedisOperate;
use app\GlobalServiceExchange\lib\Show;
use app\GlobalServiceExchange\model\User as UserModel;
use think\facade\Cache;

class User{
    public function login($openId)
    {
        #获取全部session
        $sessionAll = session();

        #根据open_id生成temp_user_id
        $gzhName = config("user.login.redis.gzh_name_abbreviation");
        $tempOpenId = md5($openId);

        #存储open_id、temp_open_id到服务器（redis string类型）
        $stringOpenIdInfoName = $gzhName.$tempOpenId;
        $isKeyExist = Cache::store('redis')->Exists($stringOpenIdInfoName);
        $isKeyExist = boolval($isKeyExist);

        #用户已登录状态
        if( isset($sessionAll["temp_open_id"]) === true && $isKeyExist === true ){
            return true;
        }

        #用户登录状态失效
        if( isset($sessionAll["temp_open_id"]) === false || $isKeyExist === false ){
            #存储temp_open_id到session 存储open_id及相关信息到服务器（redis）
            $expire = config("session.expire");
            Cache::store('redis')->Set($stringOpenIdInfoName,$openId,$expire);
            session("temp_open_id",$tempOpenId);
            return true;
        }

        #未知错误
        return false;
    }

    public function updateUserInfo($orderInfo)
    {
        #优化：不编码，在validate中验证
        #参数编码
        foreach ($orderInfo as $key => $value){
            $userInfoEncode[$key] = base64_encode($value);
        }

        $tempOpenId = session("temp_open_id");

        #根据open_id生成temp_user_id
        $gzhName = config("user.login.redis.gzh_name_abbreviation");

        #从redis中获取openId
        $stringOpenIdInfoName = $gzhName.$tempOpenId;
        $openId = Cache::store('redis')->Get($stringOpenIdInfoName);
        $isUpdateSuccess = (new UserModel())->updateUserInfoByOpenId($openId,$userInfoEncode);
        if($isUpdateSuccess===1 || $isUpdateSuccess === 0){
            return Show::success("收款信息更新成功~");
        }else{
            return Show::error("收款信息更新失败~");
        }
    }

    public function getUserInfo()
    {
        $openId = RedisOperate::getOpenId();
        $userInfo = (new UserModel())->getUserInfoByOpenId($openId);
        if ($userInfo!==null){
            $userInfo = $userInfo->toArray();
            foreach ($userInfo as $key => $value){
                $userInfoDecode[$key] = base64_decode($value);
            }
            return $userInfoDecode;
        }

        return false;
    }

    public function checkPayeeInfo($openId)
    {
        $userInfo = (new UserModel())->getUserInfoByOpenId($openId);
        if($userInfo!==null){
            $userInfoArr = $userInfo->toArray();
            if( $userInfo["payee_name"] === null || $userInfo["payee_account"] === null ){
                return Show::error("您还未填写收款人信息，请前往账户填写哦~");
            }
            return Show::success("已填写收款人信息~");
        }

        #优化：记录日志
        return Show::success("未获取到收款人信息，请联系人工客服处理哦~");
    }
}