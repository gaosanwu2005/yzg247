<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;
use Think\Log;

class Chart extends Frontend
{
    protected $noNeedLogin = ['notify','payreturn'];
    protected $noNeedRight = ['*'];


    public function _empty()
    {
        return $this->fetch();
    }
    //联系人
    public function contacts()
    {
        $user = $this->auth->getUserinfo();
        $map=['id'=>['in',json_decode($user['friends'])]];
        $users=Db::name('user')->where($map)->select();
        $this->assign('users',$users);
        return $this->fetch();
    }

    //聊天
    public function index()
    {
        $touser = input();
        $myset=config('site');
        $info = $this->auth->getUserinfo();
        $from=['id'=>$info['id'],'username'=>$info['username'],'img'=>$info['avatar']?$info['avatar']:$myset['logo']];
        $this->assign('touser',$touser);
        $this->assign('frominfo',json_encode($from));
        $this->assign('touserinfo',json_encode($touser));
        return $this->fetch();
    }


}
