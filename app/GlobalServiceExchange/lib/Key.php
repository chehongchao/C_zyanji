<?php

namespace app\GlobalServiceExchange\lib;

class Key {

    /**
     *
     * @param $appId
     * @return string
     */
    public static function Order($appId) {
        return "order_".$appId;
    }
}