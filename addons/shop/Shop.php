<?php

namespace addons\shop;

use app\common\library\Menu;
use think\Addons;

/**
 * 插件
 */
class Shop extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
	    [
	        "name" => "shop/goods",
	        "title" => "商品管理",
	        "ismenu" => 1,
	        "sublist" => [
	            [
	                "name" => "shop/goods/index",
	                "title" => "查看"
	            ],
	            [
	                "name" => "shop/goods/add",
	                "title" => "添加"
	            ],
	            [
	                "name" => "shop/goods/edit",
	                "title" => "编辑"
	            ],
	            [
	                "name" => "shop/goods/del",
	                "title" => "删除"
	            ],
	            [
	                "name" => "shop/goods/multi",
	                "title" => "批量更新"
	            ],
	            [
	                "name" => "shop/order/orderdetail",
	                "title" => "订单详情"
	            ]
	        ]
	    ],
	    [
	        "name" => "shop/order",
	        "title" => "商城订单",
	        "ismenu" => 1,
	        "sublist" => [
	            [
	                "name" => "shop/order/index",
	                "title" => "查看"
	            ],
	            [
	                "name" => "shop/order/add",
	                "title" => "添加"
	            ],
	            [
	                "name" => "shop/order/edit",
	                "title" => "编辑"
	            ],
	            [
	                "name" => "shop/order/del",
	                "title" => "删除"
	            ],
	            [
	                "name" => "shop/order/multi",
	                "title" => "批量更新"
	            ]
	        ]
	    ],
	    [
	        "name" => "shop/store",
	        "title" => "店铺数据管理",
	        "ismenu" => 1,
	        "sublist" => [
	            [
	                "name" => "shop/store/index",
	                "title" => "查看"
	            ],
	            [
	                "name" => "shop/store/add",
	                "title" => "添加"
	            ],
	            [
	                "name" => "shop/store/edit",
	                "title" => "编辑"
	            ],
	            [
	                "name" => "shop/store/del",
	                "title" => "删除"
	            ],
	            [
	                "name" => "shop/store/multi",
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
        Menu::delete("shop");
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable("shop");
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable("shop");
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
