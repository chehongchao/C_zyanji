<?php

namespace app\GlobalServiceExchange\lib;

class ClassArr {

    /**
     * 支付相关
     * @return array
     */
    public static function payClassStat() {
        return [
            "hpjpay" => "app\\GlobalServiceExchange\\lib\\pay\\HpjPay",
        ];
    }

    public static function initClass($type, $classs, $params = [], $needInstance = false) {
        if(!array_key_exists($type, $classs)) {
            return "类型：{$type} 的类库找不到";
        }
        $className = $classs[$type];

        return $needInstance == true ? (new \ReflectionClass($className))->newInstanceArgs($params) : $className;
    }
}