<?php

namespace app\GlobalServiceExchange\model;

use think\Model;

class User extends Model
{

    /**
     * @desc 通过open_id获取用户id
     * @param $openId
     * @return User|array|mixed|Model|null
     */
    public function getUserIdByOpenId($openId)
    {
        $field = "id";

        $where = [
            "open_id" => $openId,
        ];

        return $this->field($field)->where($where)->find();
    }

    public function insertUserInfo($openId)
    {
        $userInfo = [
            "open_id" => $openId,
        ];

        $this->save($userInfo);
        return $this->id;
    }

    /**
     * @desc 通过open_id更新用户信息
     * @param string $openId 用户对于当前公众号的唯一id
     * @param array $userInfo 用户收款人姓名、收款人账号组成的数组
     * @return bool
     */
    public function updateUserInfoByOpenId($openId,$userInfoEncode)
    {
        $where = [
            "open_id" => $openId,
        ];

        return $this->where($where)->save($userInfoEncode);
    }

    /**
     * @desc 通过open_id获取用户信息
     * @param string $openId 用户对于当前公众号的唯一id
     * @return bool
     */
    public function getUserInfoByOpenId($openId)
    {
        $field = "payee_name,payee_account";

        $where = [
            "open_id" => $openId,
        ];

        return $this->field($field)->where($where)->find();
    }

}