<?php

namespace addons\school;

use app\common\library\Menu;
use think\Addons;

/**
 * 插件
 */
class School extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
	    [
	        "name" => "school/info",
	        "title" => "报名信息",
	        "ismenu" => 1,
	        "sublist" => [
	            [
	                "name" => "school/info/index",
	                "title" => "查看"
	            ],
	            [
	                "name" => "school/info/add",
	                "title" => "添加"
	            ],
	            [
	                "name" => "school/info/edit",
	                "title" => "编辑"
	            ],
	            [
	                "name" => "school/info/del",
	                "title" => "删除"
	            ],
	            [
	                "name" => "school/info/multi",
	                "title" => "批量更新"
	            ]
	        ]
	    ]
	];
	Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete("school");
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable("school");
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable("school");
        return true;
    }

    /**
     * 实现钩子方法
     * @return mixed
     */
    public function testhook($param)
    {
        // 调用钩子时候的参数信息
        print_r($param);
        // 当前插件的配置信息，配置信息存在当前目录的config.php文件中，见下方
        print_r($this->getConfig());
        // 可以返回模板，模板文件默认读取的为插件目录中的文件。模板名不能为空！
        //return $this->fetch('view/info');
    }

}
