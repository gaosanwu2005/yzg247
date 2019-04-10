<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;

class Message extends Frontend
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

    public function sendlist()
    {
        $user = $this->auth->getUserinfo();
        $list = Db::name('message')->where('uid', $user['id'])->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function etc2lmc()
    {
        $tjuser = input('tjuser');
        $this->assign('tjuser', $tjuser);
        return $this->fetch();
    }
}
