<?php

namespace app\GlobalServiceExchange\lib;

class Show {
    /**
     * @param array $data
     * @param string $message
     * @return \think\response\Json
     */
    public static function success(string $message = "OK", array $data = []) {
        $result = [
            "status" => config("status.success"),
            "message" => $message,
            "data" => $data,
        ];

        return json($result);
    }

    /**
     * @param array $data
     * @param string $message
     * @param int $status
     * @return \think\response\Json
     */
    public static function error(string $message = "error", int $status = 0, array $data = []) {

        $result = [
            "status" => $status,
            "message" => $message,
            "data" => $data,
        ];

        return json($result);
    }
}