<?php

namespace app\admin\model;

use think\Model;

class ShopOrder extends Model
{
    // 表名
    protected $name = 'shop_order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'order_status_text',
        'add_time_text',
        'shipping_time_text',
        'confirm_time_text',
        'pay_time_text'
    ];
    

    
    public function getOrderStatusList()
    {
        return ['0' => __('Order_status 0'),'1' => __('Order_status 1'),'2' => __('Order_status 2'),'3' => __('Order_status 3'),'4' => __('Order_status 4'),'5' => __('Order_status 5')];
    }     


    public function getOrderStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['order_status'];
        $list = $this->getOrderStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAddTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['add_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getShippingTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['shipping_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getConfirmTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['confirm_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPayTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['pay_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAddTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setShippingTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setConfirmTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setPayTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
