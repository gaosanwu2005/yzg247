<?php

namespace app\index\controller;

use app\common\controller\Frontend;

class Info extends Frontend
{
    protected $noNeedLogin = ['test', 'password2'];
    protected $noNeedRight = ['*'];
    public function index()
    {
        return $this->fetch();
    }

    public function _empty()
    {
        return $this->fetch();
    }
}
