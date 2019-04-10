<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;

class Quotation extends Frontend
{
    protected $noNeedRight = ['*'];
    /**
     * 商城
     *Create by xiaoniu
     */
    public function index()
    {
        $list=Db::name('goods')->where(array('is_on'=>1,  'goods_id' => array('gt', '1')))->order('sort asc')->select();
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function _empty()
    {
        return $this->fetch();
    }

    /**
     * 查看矿机
     *Create by xiaoniu
     */
    public function shopdetail(){

        $id=$this->request->request('gid');
        $goods=Db::name('goods')->where(array('goods_id'=>$id))->cache(true,300)->find();
        $this->assign('info',$goods);
        return $this->fetch();
    }
}
