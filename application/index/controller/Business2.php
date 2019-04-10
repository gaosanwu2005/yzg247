<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use Think\Db;

class Business2 extends Frontend
{
    protected $noNeedRight = ['*'];
    public function index()
    {
        return $this->fetch();
    }

    public function _empty()
    {
        return $this->fetch();
    }

    /**
     * 点击付款 信息
     */
    public function skdetail()
    {
        $ppid = input('get.ppid');
        $ppmx =model('ppmx2');
        $rspp = $ppmx->get($ppid);
        $rsuser = Db::name('user')->where(array('id' => $rspp['userid1']))->find();
        $tjuser = Db::name('user')->where(array('username' => $rsuser['tjuser']))->find();
        $this->assign('list', $rspp->toArray());
        $this->assign('tjuser', $tjuser);
        $this->assign('rsuser', $rsuser);
        return $this->fetch();
    }

    /**
     *  买家待付款 信息
     */
    public function dkdetail()
    {
        $ppid = input('get.ppid');
        $ppmx =model('ppmx2');
        $rspp = $ppmx->get($ppid);
        $rsuser = Db::name('user')->where(array('id' => $rspp['userid']))->find();
        $tjuser = Db::name('user')->where(array('username' => $rsuser['tjuser']))->find();

        $this->assign('rspp', $rspp->toArray());
        $this->assign('tjuser', $tjuser);
        $this->assign('rsuser', $rsuser);
        return $this->fetch();
    }

    /**
     * 上传凭证
     */
    public function pay()
    {
        $ppid = input('get.ppid');
        $ppmx =model('ppmx2');
        $rspp = $ppmx->get($ppid);
        $this->assign('rspp', $rspp->toArray());
        return $this->fetch();
    }

    /**
     * 打款信息
     */
    public function payinfo()
    {
        $ppid = input('get.ppid');
        $ppmx =model('ppmx2');
        $rspp = $ppmx->get($ppid);
        $this->assign('rspp', $rspp->toArray());
        return $this->fetch();
    }

    /**
     * 投诉信息
     */
    public function touinfo()
    {
        $ppid = input('get.ppid');
        $ppmx =model('ppmx2');
        $rspp = $ppmx->get($ppid);
        $this->assign('rspp', $rspp->toArray());
        return $this->fetch();
    }

    /**
     * 投诉信息
     */
    public function shamts()
    {
        $ppid = input('ppid');
        $this->assign('ppid', $ppid);
        return $this->fetch();
    }
}
