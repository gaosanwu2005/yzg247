<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;

class Announce extends Frontend
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _empty()
    {
        return $this->fetch();
    }

    public function index()
    {
        $list = Db::name('articale')->where('category_id', 30)->select();
        $this->assign('list', $list);

        return $this->fetch();
    }

    public function xwmb()
    {
        $id = input('id');
        $list = Db::name('articale')->find($id);
//        $imgs='';
//        if($list['images']){
//            $imgs = explode(',',$list['images']);
//        }
        $this->assign('list', $list);
//        $this->assign('imgs', $imgs);
        return $this->fetch();

//
//        $id = input('id');
//
//        $list=0;
//        $imgs='';
//        if($list['images']){
//            $imgs = explode(',',$list['images']);
//        }
//        $this->assign('imgs', $imgs);
//        $this->assign('list', $list);
//        return $this->fetch();
    }

    /**
     * 新闻资讯.
     *
     * @array
     * @list
     */
    public function news()
    {
        $news_type = input('type');
        if ($news_type == 1) {
            $list = Db::name('articale')->where('category_id=86')->limit(5)->select();
            $type = 1; //公告
        } else {
            $list = Db::name('articale')->where('category_id=35')->limit(5)->select();
            $type = 2; //新闻
        }
        $this->assign('type', $type);
        $this->assign('list', $list);

        return view('news');
    }
}
