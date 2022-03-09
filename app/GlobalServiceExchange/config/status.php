<?php

/*
 * 该文件主要是存放业务状态码相关的配置
*/


return [
    'error' => 0,
    'success' => 1,
    'not_login' => -1,
    'user_is_register' => -2,
    'method_not_found' => -3,

    //mysql的相关配置
    'mysql' => [
        'table_normal' => 1,  //正常
        'table_pedding' => 0,  //待审核
        'table_delete' => -1,  //已删除(假删除)
    ],

    //手机验证码相关配置
    'mobile_verification_code' => [
        'code_length_4' => '4',
        'code_length_6' => '6',
    ]
];
