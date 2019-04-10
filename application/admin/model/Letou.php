<?php

namespace app\admin\model;

use think\Model;

class Letou extends Model
{
    // 表名
    protected $name = 'letou';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'ktime_text'
    ];
    

    



    public function getKtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['ktime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setKtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
