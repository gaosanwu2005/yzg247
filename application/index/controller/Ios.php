<?php

namespace app\index\controller;

use think\Config;
use think\Controller;

class Ios extends controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function _empty()
    {
        \config('default_ajax_return','html');
        return $this->fetch();
    }

}
