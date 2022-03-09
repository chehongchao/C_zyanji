<?php

namespace app\GlobalServiceExchange\model;

use think\Model;

class Test extends Model {
    public function test($openId)
    {
        $userInfo = [
            "open_id" => $openId,
        ];

        $this->save($userInfo);
        return $this->id;
    }
}