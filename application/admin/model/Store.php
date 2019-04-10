<?php

namespace app\admin\model;

use think\Model;

class Store extends Model
{
    // 表名
    protected $name = 'store';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'store_state_text',
        'store_time_text',
        'store_end_time_text',
        'store_recommend_text',
        'store_workingtime_text',
        'store_free_time_text',
        'ensure_text'
    ];
    

    
    public function getStoreStateList()
    {
        return ['0' => __('Store_state 0'),'1' => __('Store_state 1'),'2' => __('Store_state 2')];
    }     

    public function getStoreRecommendList()
    {
        return ['0' => __('Store_recommend 0'),'1' => __('Store_recommend 1')];
    }     

    public function getEnsureList()
    {
        return ['0' => __('Ensure 0'),'1' => __('Ensure 1')];
    }     


    public function getStoreStateTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['store_state'];
        $list = $this->getStoreStateList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStoreTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['store_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStoreEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['store_end_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStoreRecommendTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['store_recommend'];
        $list = $this->getStoreRecommendList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStoreWorkingtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['store_workingtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStoreFreeTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['store_free_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEnsureTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['ensure'];
        $list = $this->getEnsureList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setStoreTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setStoreEndTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setStoreWorkingtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setStoreFreeTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
