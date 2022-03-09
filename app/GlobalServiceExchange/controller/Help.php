<?php

namespace app\GlobalServiceExchange\controller;

class Help{
    //客服:优化
    public function customerService()
    {
        return view(
            "",
            [
                "title" => "人工客服",
            ]
        );
    }
}