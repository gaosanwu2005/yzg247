<?php

namespace app\admin\model;

use think\Model;

class ShopGoods extends Model
{
    // 表名
    protected $name = 'shop_goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'is_real_text',
        'is_on_sale_text',
        'is_free_shipping_text',
        'on_time_text',
        'is_recommend_text',
        'is_new_text',
        'is_hot_text',
        'prom_type_text'
    ];
    

    
    public function getIsRealList()
    {
        return ['0' => __('Is_real 0'),'1' => __('Is_real 1')];
    }     

    public function getIsOnSaleList()
    {
        return ['0' => __('Is_on_sale 0'),'1' => __('Is_on_sale 1')];
    }     

    public function getIsFreeShippingList()
    {
        return ['0' => __('Is_free_shipping 0'),'1' => __('Is_free_shipping 1')];
    }     

    public function getIsRecommendList()
    {
        return ['0' => __('Is_recommend 0'),'1' => __('Is_recommend 1')];
    }     

    public function getIsNewList()
    {
        return ['0' => __('Is_new 0'),'1' => __('Is_new 1')];
    }     

    public function getIsHotList()
    {
        return ['0' => __('Is_hot 0'),'1' => __('Is_hot 1')];
    }     

    public function getPromTypeList()
    {
        return ['0' => __('Prom_type 0'),'1' => __('Prom_type 1'),'2' => __('Prom_type 2'),'3' => __('Prom_type 3')];
    }     


    public function getIsRealTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['is_real'];
        $list = $this->getIsRealList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsOnSaleTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['is_on_sale'];
        $list = $this->getIsOnSaleList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsFreeShippingTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['is_free_shipping'];
        $list = $this->getIsFreeShippingList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getOnTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['on_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getIsRecommendTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['is_recommend'];
        $list = $this->getIsRecommendList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsNewTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['is_new'];
        $list = $this->getIsNewList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsHotTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['is_hot'];
        $list = $this->getIsHotList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPromTypeTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['prom_type'];
        $list = $this->getPromTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setOnTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
