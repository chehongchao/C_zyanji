<?php

namespace app\GlobalServiceExchange\middleware;

#declare (strict_types = 1);

use app\GlobalServiceExchange\lib\Show;
use think\facade\Cache;

class Auth
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return \think\response\Json|\think\response\Redirect
     */
    public function handle($request, \Closure $next)
    {
        #如果是支付通知回调地址，放行
        $pathInfo = request()->pathinfo();
        if(preg_match("#pay/notify#i",$pathInfo)){
            return $next($request);
        }

        #先获取登录状态（已登录|未登录）、判断是否是登录页面
        $isLogin = $this->isLogin();
        $isLoginPage = $this->isLoginPage();

        switch ($isLogin)
        {
            #已登录状态
            case true:
                #如果是登录页面，跳转到首页（商品页面）
                if($isLoginPage===true){
                    $redirectUrl = config("common.host")."/GlobalServiceExchange/good/index.html";
                    return redirect($redirectUrl);
                }

                #否则放行
                return $next($request);

            #未登录状态
            case false:
                #如果是登录页面，放行
                if($isLoginPage===true){
                    return $next($request);
                }

                #如果是POST请求，返回json，由前端跳转到登录页面
                if(request()->isPost()){
                    $message = "登录已过期，即将为您跳转至登录页面~";
                    return Show::error($message,301);
                }

                #非POST请求，直接跳转到登录页面
                $redirectUrl = config("common.host")."/GlobalServiceExchange/user/login.html";
                return redirect($redirectUrl);
        }
    }

    //方法：判断用户是否登录
    public function isLogin()
    {
        #获取所有session内容、temp_open_id
        $sessionAll = session();

        #如果session中存在temp_open_id、再判断redis中是否存在对应的key
        if(isset($sessionAll["temp_open_id"])===true){
            $gzhName = config("user.login.redis.gzh_name_abbreviation");
            $tempOpenId = $sessionAll["temp_open_id"];
            $stringOpenIdInfoName = $gzhName.$tempOpenId;
            $isKeyExist = Cache::store('redis')->Exists($stringOpenIdInfoName);
            $isKeyExist = boolval($isKeyExist);

            #用户已登录状态(放行)
            if( $isKeyExist === true ){
                return true;
            }

            #用户登录状态失效
            if( $isKeyExist === false ){
                return false;
            }
        }

        #如果session中不存在temp_open_id、需要重新登录，返回false;
        return false;
    }

    //方法：判断是否是登录页面
    public function isLoginPage()
    {
        $pathInfo = request()->pathinfo();
        if( preg_match("#user/login#i",$pathInfo) ){
            return true;
        }

        return false;
    }
}
