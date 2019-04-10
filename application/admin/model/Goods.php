<?php

namespace app\admin\model;

use think\Model;

class Goods extends Model
{
    // 表名
    protected $name = 'goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'on_time_text',
        'is_on_text'
    ];
    

    
    public function getIsOnList()
    {
        return ['1' => __('Is_on 1'),'0' => __('Is_on 0')];
    }     


    public function getOnTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['on_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getIsOnTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['is_on'];
        $list = $this->getIsOnList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setOnTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
