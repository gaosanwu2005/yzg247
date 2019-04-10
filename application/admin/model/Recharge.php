<?php

namespace app\admin\model;

use think\Model;

class Recharge extends Model
{
    // 表名
    protected $name = 'recharge';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'ctime_text',
        'pay_time_text',
        'pay_status_text'
    ];
    

    
    public function getPayStatusList()
    {
        return ['0' => __('Pay_status 0'),'1' => __('Pay_status 1'),'2' => __('Pay_status 2')];
    }     


    public function getCtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['ctime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPayTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['pay_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPayStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['pay_status'];
        $list = $this->getPayStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setCtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setPayTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
