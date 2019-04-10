<?php

namespace app\admin\model;

use think\Model;

class Articale extends Model
{
    // 表名
    protected $name = 'articale';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'refreshtime_text',
        'switch_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getSwitchList()
    {
        return ['1' => __('Switch 1'),'2' => __('Switch 2')];
    }     


    public function getRefreshtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['refreshtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getSwitchTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['switch'];
        $list = $this->getSwitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setRefreshtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
