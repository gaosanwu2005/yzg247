<?php

namespace app\admin\model;

use think\Model;

class Bureau extends Model
{
    // 表名
    protected $name = 'bureau';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'idtype_text',
        'status_text',
        'bureau_type_text'
    ];
    

    
    public function getIdtypeList()
    {
        return ['3' => __('Idtype 3')];
    }     

    public function getStatusList()
    {
        return ['3' => __('Status 3')];
    }     

    public function getBureauTypeList()
    {
        return ['1' => __('Bureau_type 1'),'2' => __('Bureau_type 2'),'3' => __('Bureau_type 3')];
    }     


    public function getIdtypeTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['idtype'];
        $list = $this->getIdtypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getBureauTypeTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['bureau_type'];
        $list = $this->getBureauTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
