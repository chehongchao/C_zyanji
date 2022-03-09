<?php

namespace app\GlobalServiceExchange\exception;

use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\facade\Cache;
use think\Response;
use Throwable;
use app\GlobalServiceExchange\lib\Show;

class All extends Handle
{
    public function render($request, Throwable $e): Response
    {
        // 参数验证错误
/*        if ($e instanceof ValidateException) {
            return json($e->getError(), 422);
        }*/

        /*        // 请求异常
                if ($e instanceof HttpException && $request->isAjax()) {
                    return response($e->getMessage(), $e->getStatusCode());
                }*/


        #可在此自定义http异常处理方式
        if ($e instanceof HttpException ) {
            return Show::error("页面不存在");
        }

        // 其他错误
        $errorMessage = "出错文件位置：".$e->getFile()."\n"."报错的行数：".$e->getLine()."\n"."错误信息：".$e->getMessage();
        Cache::store('redis')->hSet("gsx_jdwkj233_cn_exception",time(),$errorMessage);
        #return Show::error($errorMessage,[],$e->getCode());
        return Show::error("请求失败，请重试~",$e->getCode());
    }
}