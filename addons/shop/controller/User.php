<?php

namespace addons\shop\controller;
 
use think\addons\Controller;  
use think\Config;
use think\Cookie;
use think\Db;
use think\Hook;
use think\Session;
use think\Validate;
use think\Request;

/**
 * 商城会员中心
 */
class User extends Controller
{

    protected $layout = '';
    protected $noNeedLogin = ['login', 'reg', 'getpwd','appdownload','changepwd'];
    protected $noNeedRight = ['*'];
   
    public function _initialize()
    {
        parent::_initialize();
         \config('default_ajax_return','html');  
        $this->assign('xing', -5*15+150);
        $this->assign('sidebar', 4);
        $this->assign('hyrs',$this->auth->getUserinfo());
    }  
    public function _empty()
    { 
        return $this->fetch();
    }

    
    public function uploadgoods()
    {
        $category= db('category')->where('type','shop_goods')->select(); 
        $this->assign('category', $category);
        return $this->fetch();
    } 

    /**
     * 入驻商家
     *Create by xiaoniu
     */
    public function openshop()
    {

        $list = db('category')->where(['type'=>'shop_goods','pid'=>0])->select();
        $this->assign('cat', $list);
        return $this->fetch();
    }

    /**
     * 商城订单
     */
    public function order()
    {
        $user = $this->auth->getUserinfo();
        $list = Db::table('fa_shop_order')->alias('a')
            ->join('fa_shop_order_goods b', 'b.order_id = a.order_id')
            ->join('fa_shop_goods c', 'c.goods_id = b.goods_id')
            ->where(['a.user_id'=>$user['id']])->order('a.order_id desc')->select();

        foreach ($list as $item) {
            $tmp[$item['order_sn']][] = $item;
        }
        $tmp = isset($tmp)? $tmp : [];
        $order_status = ['0'=>'待付款','1'=>'已付款待发货','2'=>'已发货待确认','3'=>'已完成','4'=>'已完成','5'=>'已取消'];
        $this->assign('order_status', $order_status);
        $this->assign('list', $tmp);
        return $this->fetch();
    }
 
    /**
     * 商城卖单
     */
    public function shoporder()
    {
        $user = $this->auth->getUserinfo();
        $list = Db::table('fa_shop_order')->alias('a')
            ->join('fa_shop_order_goods b', 'b.order_id = a.order_id')
            ->join('fa_shop_goods c', 'c.goods_id = b.goods_id')
            ->where(['a.shop_id'=>$user['id']])->order('a.order_id desc')->select();

        $order_status = ['0'=>'待付款','1'=>'已付款待发货','2'=>'已发货待确认','3'=>'已完成','4'=>'已完成','5'=>'已取消'];
        $this->assign('order_status', $order_status);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 店铺订单
     *Create by xiaoniu
     */
    public function sendlink4()
    {
        $id= input('order_id');
        $order = Db::table('fa_shop_order')->alias('a')
            ->join('fa_shop_order_goods b', 'b.order_id = a.order_id')
            ->join('fa_shop_goods c', 'c.goods_id = b.goods_id')
            ->where(['a.order_id'=>$id])->order('a.order_id desc')->find();

        $this->assign('order', $order);
        return $this->fetch();
    }

    /**
     * 商城C店铺查看商品
     */
    public function shopgoods()
    {
        $user = $this->auth->getUserinfo();
        $list = Db::table('fa_shop_goods')
            ->where(['shop_id'=>$user['id']])->order('goods_id desc')->select();
        dump($list);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 商城C店铺编辑商品
     */
    public function editgoods()
    {
        $user = $this->auth->getUserinfo();
        $good_id = input('gid');
        $list = Db::table('fa_shop_goods')->find($good_id);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 实体申请
     */
    public function realshop()
    {
        $list = db('category')->where(['type'=>'shop_goods','pid'=>0])->select();
        $this->assign('cat', $list);
        return $this->fetch();
    }
     
    /**
     * 我的收藏
     */
    public function collection()
    {
        $user = $this->auth->getUserinfo();
        $list = array();
        if (!empty($user['collection'])) {
            $new = json_decode($user['collection'], true);
            $list = Db::name('shop_goods')->where(['goods_id'=>['in',$new]])->select();

        }
        $this->assign('list',$list);
        return $this->fetch();
    } 
}
