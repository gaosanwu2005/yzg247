<?php

namespace app\admin\model;

use think\Model;

class SchoolInfo extends Model
{
    // 表名
    protected $name = 'school_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'addtime_text',
        'is_on_text'
    ];
    

    
    public function getIsOnList()
    {
        return ['1' => __('Is_on 1'),'0' => __('Is_on 0')];
    }     


    public function getAddtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['addtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getIsOnTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['is_on'];
        $list = $this->getIsOnList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setAddtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
