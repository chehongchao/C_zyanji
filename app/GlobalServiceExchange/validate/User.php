<?php

namespace app\GlobalServiceExchange\validate;

use think\Validate;

class User extends Validate{

    protected $rule = [
        "open_id" => "require|isOrderExist",

        "payee_name" => "require",
        "payee_account" => "require",
    ];

    protected $message = [
        "open_id.require" => "open_id不能为空",

        "payee_name.require" => "收款人姓名不能为空",
        "payee_account.require" => "收款人账号不能为空",
    ];

    protected $scene = [
        'login'  =>  ['open_id'],
    ];
}