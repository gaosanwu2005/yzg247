<?php

namespace app\index\controller;
use think\Controller;

class Web extends controller
{

    public function index()
    {
        $myset=config('site');
        $opentime = date('m/d/Y H:i:s',strtotime($myset['kai'])+7200);
        $this->assign('opentime', $opentime);
        $this->assign('site', $myset);
        return $this->fetch();
    }

    public function _empty()
    {
        return $this->fetch();
    }
}
