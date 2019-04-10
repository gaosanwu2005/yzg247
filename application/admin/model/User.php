<?php

namespace app\admin\model;

use think\Db;
use think\Model;

class User extends Model
{
    // 表名
    protected $name = 'user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'level_text',
        'gender_text',
        'prevtime_text',
        'logintime_text',
        'loginfailure_text',
        'jointime_text',
        'status_text'
    ];
    

    
    public function getLevelList()
    {
       $levels= Db::table('fa_user_level')->cache(true,60)->select();
        foreach ($levels as $level){
            $tmp[$level['level_id']]=$level['level_name'];
        }
        return $tmp;
    }     

    public function getGenderList()
    {
        return ['0' => __('Gender 0'),'1' => __('Gender 1')];
    }     

    public function getLoginfailureList()
    {
        return ['1) unsigne' => __('1) unsigne')];
    }     

    public function getStatusList()
    {
        return ['0' => __('Status 0'),'1' => __('Status 1')];
    }     


    public function getLevelTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['level'];
        $list = $this->getLevelList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getGenderTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['gender'];
        $list = $this->getGenderList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['prevtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['logintime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getLoginfailureTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['loginfailure'];
        $list = $this->getLoginfailureList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['jointime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setPrevtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setLogintimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setJointimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
