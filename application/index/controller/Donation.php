<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Controller;
use think\Db;

/**
 * 慈善捐助
 */
class Donation extends Frontend
{
    protected $layout = '';
    protected $noNeedRight = ['*'];
    protected $model = null;

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 列表
     */
    public function index()
    {
        $list = Db::name('donation')->where('switch',1)->select();

        //用户id
        $user = $this->auth->getUserinfo();
        $love_time = DB::name('user')->sum('love_number');
        $this->assign('love_time',$love_time);
        $this->assign('list',$list);
        $this->assign('user',$user);
        return $this->fetch();
    }

    /**
     * 详情
     */
    public function detail($id)
    {
        $info = Db::name('donation')->where('id',$id)->find();
        $jindu = $info['amount_donated'] / $info['amount'] * 100;
        $this->assign('jindu',$jindu);
        $this->assign('info',$info);
        return $this->fetch();
    }

}