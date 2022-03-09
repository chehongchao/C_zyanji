<?php

namespace app\GlobalServiceExchange\model;

use think\Model;

class Good extends Model{
    /**
     * 获取分页列表
     * @param $where
     * @param $limit
     * @return array
     */
    public function getGlobalServiceExchangeList($where, $limit)
    {
        try {

            $list = $this->where($where)->order('id', 'desc')->paginate($limit);
        } catch(\Exception $e) {
            return dataReturn(-1, $e->getMessage());
        }

        return dataReturn(0, 'success', $list);
    }

    /**
     * 添加信息
     * @param $param
     * @return $array
     */
    public function addGlobalServiceExchange($param)
    {
        try {

            // TODO 去重校验

            $param['add_time'] = date('Y-m-d H:i:s');
            $this->insert($param);
        } catch(\Exception $e) {

            return dataReturn(-1, $e->getMessage());
        }

        return dataReturn(0, 'success');
    }

    /**
     * 根据id获取信息
     * @param $id
     * @return array
     */
    public function getGlobalServiceExchangeById($id)
    {
        try {

            $info = $this->where('id', $id)->find();
        } catch(\Exception $e) {

            return dataReturn(-1, $e->getMessage());
        }

        return dataReturn(0, 'success', $info);
    }

    /**
     * 编辑信息
     * @param $param
     * @return array
     */
    public function editGlobalServiceExchange($param)
    {
        try {

            // TODO 去重校验

            $param['update_time'] = date('Y-m-d H:i:s');
            $this->where('id', $param['id'])->update($param);
        } catch(\Exception $e) {

            return dataReturn(-1, $e->getMessage());
        }

        return dataReturn(0, 'success');
    }

    /**
     * 删除信息
     * @param $id
     * @return array
     */
    public function delGlobalServiceExchangeById($id)
    {
        try {

            // TODO 不可删除校验

            $this->where('id', $id)->delete();
        } catch(\Exception $e) {

            return dataReturn(-1, $e->getMessage());
        }

        return dataReturn(0, 'success');
    }
}