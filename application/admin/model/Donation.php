<?php

namespace app\admin\model;

use think\Model;

class Donation extends Model
{
    // 表名
    protected $name = 'donation';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'switch_text'
    ];
    

    
    public function getSwitchList()
    {
        return ['0' => __('Switch 0'),'1' => __('Switch 1')];
    }     


    public function getSwitchTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['switch'];
        $list = $this->getSwitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
