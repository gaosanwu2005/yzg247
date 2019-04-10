<?php

namespace app\admin\model;

use think\Model;

class Message extends Model
{
    // 表名
    protected $name = 'message';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'addtime_text',
        'retime_text',
        'isreply_text'
    ];
    

    
    public function getIsreplyList()
    {
        return ['0' => '待回复','1' =>'已回复'];
    }     


    public function getAddtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['addtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getRetimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['retime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getIsreplyTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['isreply'];
        $list = $this->getIsreplyList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setAddtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setRetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
