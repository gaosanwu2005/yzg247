<?php

namespace addons\shop\controller;
use think\addons\Controller; 
use think\Db;
use tree\Tree2;
class Goods extends Controller
{
    protected $noNeedLogin = 'index';
    protected $noNeedRight = '*';
    protected $layout = '';
    public function _initialize()
    {
        parent::_initialize();
        \config('default_ajax_return','html'); 
    }

    public function _empty()
    {
        return $this->fetch();
    }
    /**
     * 商品详情页
     */
    public function goodsinfo(){
        $goods_id = input("id");
        $goods = Db::name('shop_goods')->where("goods_id",$goods_id)->find();
        if(empty($goods)){
            $this->error('此商品不存在或者已下架');
        }
        $user=array();
        if($goods['shop_id']>0){
            $user= Db::name('user')->find($goods['shop_id']);
        }
        if(is_array($user)){
            $all= array_merge($goods,$user);
        }else{
            $all= $goods;
        }

        $images = explode(',',$goods['images']);
        $spec_type = json_decode($goods['spec_type'],true);
        $this->assign('info',$all);
        $this->assign('images',$images);
        $this->assign('spec_type',$spec_type);
        return $this->fetch();
    }

    /**
     * 商品分类
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function category(){
        $categorys= Db::name('category')->where('type','shop_goods')->field('pid as tjid,id,name,image,description,status')->select();
        $tree = new Tree2();
        $tree->init($categorys);
        $treeStr = $tree->getTreeArray5(0);

        $this->assign('treeStr',$treeStr);
        return $this->fetch();
    }

    /**
     * 商品搜索
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function search(){
        $goods= Db::name('shop_goods')->where('is_on_sale',1)->select();

        $this->assign('goods',$goods);

        return $this->fetch();
    }

    /**
     * 商品搜索结果
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodslist(){
        $find= input('find');
        $map['is_on_sale']=1;
        $map['goods_name']=['like','%'.$find.'%'];
        $hot_goods =Db::name('shop_goods')->where($map)->order('goods_id DESC')->limit(20)->cache(true)->select();//首页热卖商品
        $this->assign('hot_goods',$hot_goods);
        return $this->fetch();
    }

    /**
     * 商品分类搜索结果
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodslist2(){
        $find= input('find');
        $map['is_on_sale']=1;
        $map['category_id']=$find;
        $hot_goods =Db::name('shop_goods')->where($map)->order('goods_id DESC')->limit(20)->cache(true)->select();//首页热卖商品
        $this->assign('hot_goods',$hot_goods);
        return $this->fetch('goodslist');
    }

    /**
     * 商品类型搜索结果
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodslist3(){
        $find= input('find');
        $map['is_on_sale']=1;
        if(!empty($find)){
            $map[$find]=1;
        }

        $hot_goods =Db::name('shop_goods')->where($map)->order('goods_id DESC')->limit(20)->cache(true)->select();//首页热卖商品
        $this->assign('hot_goods',$hot_goods);
        return $this->fetch('goodslist');
    }

    /**
     * 精品商城 报单产品
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodslist4(){

        $map['is_on_sale']=1;
        $map['type']=2;

        $hot_goods =Db::name('shop_goods')->where($map)->order('goods_id DESC')->cache(true)->select();//首页热卖商品
        $this->assign('hot_goods',$hot_goods);
        return $this->fetch('goodslist');
    }

    /**
     * C店铺商品列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodslist5(){
        $find= input('find');
        $map['is_on_sale']=1;
        if(!empty($find)){
            $map['shop_id']=$find;
        }

        $hot_goods =Db::name('shop_goods')->where($map)->order('goods_id DESC')->limit(20)->cache(true)->select();//首页热卖商品
        $this->assign('hot_goods',$hot_goods);
        return $this->fetch('goodslist');
    }
}