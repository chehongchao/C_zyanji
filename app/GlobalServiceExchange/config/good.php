<?php


return [

    //GSX查询相关信息
    "query" => [

        "coverage_purchase" => [
            "type" => "coverage_purchase",
            "api_type" => "coverage-purchase",
            "price" => 0.26,
            "title" => "苹果激活日期查询",
            "body" => "查询苹果设备的激活日期",
            "query_api_url" => "",
            #测试地址
            #"query_api_url" => "https://test.jdwkj233.cn/gsx/query.php?query_type=coverage_purchase",
        ],

        "details_carrier" => [
            "type" => "details_carrier",
            "api_type" => "details-carrier",
            "price" => 3.4,
            "title" => "苹果 验机报告 查询",
            "body" => "查询苹果设备的 验机报告",
            "query_api_url" => "",
            #测试地址
            #"query_api_url" => "https://test.jdwkj233.cn/gsx/query.php?query_type=details_carrier",
        ],

        "carrier" => [
            "type" => "carrier",
            "api_type" => "carrier",
            "price" => 2.08,
            "title" => "苹果 网络锁、运营商、购买地点 查询",
            "body" => "查询苹果设备的 网络锁、运营商、购买地点",
            "query_api_url" => "",
        ],

        "icloud" => [
            "type" => "icloud",
            "api_type" => "icloud",
            "price" => 0.78,
            "title" => "苹果 激活锁、ID黑白 查询",
            "body" => "查询苹果设备的 激活锁、ID黑白",
            "query_api_url" => "",
        ],

        "purchase" => [
            "type" => "purchase",
            "api_type" => "purchase",
            "price" => 1.04,
            "title" => "苹果 生产日期、购买日期、激活状态、激活锁、保修、官换、BS、已更换、借出设备 查询",
            "body" => "查询苹果设备的 生产日期、购买日期、激活状态、激活锁、保修、官换、BS、已更换、借出设备",
            "query_api_url" => "",
        ],
    ],

    //订单相关信息
    "order" => [
        "redis" => [
            "hash_map_name_order_info" => "gsx_jdwkj233_cn_order_info",
            "hash_map_name_order_payment_status" => "gsx_jdwkj233_cn_order_payment_status",
            "hash_map_name_original_query_result" => "gsx_jdwkj233_cn_original_query_result",
            "sorted_set_name_order_status" => "gsx_jdwkj233_cn_order_status",
        ],
    ],

    "query_result_example_img_url" => [
        "coverage_purchase" => "https://tvax2.sinaimg.cn/large/006Bi2D5ly1h007ue72bnj30ku194n0q.jpg",
        "details_carrier" => [
            "https://tva4.sinaimg.cn/large/006Bi2D5ly1h00b176zknj30ku1abafc.jpg",
            "https://tvax3.sinaimg.cn/large/006Bi2D5ly1h00b17rsetj30ku19k0xn.jpg",
        ],
        "carrier" => "https://tva4.sinaimg.cn/large/006Bi2D5ly1h007ugg9x4j30ku194n0h.jpg",
        "icloud" => "https://tva1.sinaimg.cn/large/006Bi2D5ly1h007uh2863j30ku19441o.jpg",
        "purchase" => "https://tva1.sinaimg.cn/large/006Bi2D5ly1h007uhe0k4j30j21aojw5.jpg",
    ],
];