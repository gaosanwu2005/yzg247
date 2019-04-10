<?php

namespace addons\webmaintain;

use think\Addons;
use think\exception\HttpResponseException;

/**
 * 插件
 */
class Webmaintain extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {

        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {

        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {

        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {

        return true;
    }

    /**
     * 模型初始化
     * @throws \Exception
     */
    public function moduleInit()
    {
        if (request()->module() != 'admin') {
            $this->closeWebsite();
        }
    }

    /**
     * 插件初始化
     * @throws \Exception
     */
    public function addonBegin()
    {
        $this->closeWebsite();
    }

    /**
     * 关闭站点
     * @throws \Exception
     */
    protected function closeWebsite()
    {
        $cfg = $this->getConfig();
        $request = \think\Request::instance();
        if ($cfg['on_off'] == 1) {
            //获取插件配置信息
            if ($request->isAjax() || $request->module() == 'api') {
                $result = [
                    'code' => 0,
                    'msg'  => $cfg['site_1st'],
                    'time' => $request->server('REQUEST_TIME'),
                    'data' => null,
                ];
                // 如果未设置类型则自动判断
                $type = $request->param(config('var_jsonp_handler')) ? 'jsonp' : 'json';
            } else {
                $result = $this->view->fetch('index/view', ['cfg' => $cfg]);
                $type = 'html';
            }
            $response = \think\Response::create($result, $type, 200);
            throw new HttpResponseException($response);
        }
    }

}
