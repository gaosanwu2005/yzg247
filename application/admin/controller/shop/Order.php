<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{
    
    /**
     * ShopOrder模型对象
     * @var \app\admin\model\ShopOrder
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('ShopOrder');
        $this->view->assign("orderStatusList", $this->model->getOrderStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function orderdetail($ids)
    {
        $map['order_id']=$ids;
        $order = model('shop_order')->where($map)->find();
        $ordergoods = db('shop_order_goods')->where($map)->select();

        $this->view->assign("row", $order->toArray());
        $this->view->assign("ordergoods", $ordergoods);

        return $this->view->fetch();

    }
}
