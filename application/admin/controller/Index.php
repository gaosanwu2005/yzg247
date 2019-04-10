<?php

namespace app\admin\controller;

use addons\epay\library\Service;
use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\Config;
use think\Hook;
use think\Validate;

/**
 * 后台首页
 * @internal
 */
class Index extends Backend
{

    protected $noNeedLogin = ['login','test'];
    protected $noNeedRight = ['index', 'logout'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 后台首页
     */
    public function index()
    {
        //左侧菜单
        $menulist = $this->auth->getSidebar([
            'dashboard' => 'hot',
            'addon'     => ['new', 'red', 'badge'],
            'auth/rule' => __('Menu'),
            'general'   => ['new', 'purple'],
                ], $this->view->site['fixedpage']);
        $action = $this->request->request('action');
        if ($this->request->isPost())
        {
            if ($action == 'refreshmenu')
            {
                $this->success('', null, ['menulist' => $menulist]);
            }
        }
        $this->view->assign('menulist', $menulist);
        $this->view->assign('title', __('Home'));
        return $this->view->fetch();
    }

//    public function test()
//    {
////        //创建支付对象
////        $pay = Service::createPay('alipay');
////
////        //构建订单信息
////        $order = [
////            'out_trade_no' => date("YmdHis"),//你的订单号
////            'total_amount' => 0.01,//单位元
////            'subject'      => 'FastAdmin企业支付插件测试订单',
////        ];
////
////        //跳转或输出
////        return $pay->wap($order)->send();
//
//        update_user_tui(2);
//
//
//    }

    /**
     * 管理员登录
     */
    public function login()
    {

        $url = $this->request->get('url', 'index/index');
        if ($this->auth->isLogin())
        {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        if ($this->request->isPost())
        {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,30',
                '__token__' => 'token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];
            //自带验证
//            if (Config::get('fastadmin.login_captcha'))
//            {
//                $rule['captcha'] = 'require|captcha';
//                $data['captcha'] = $this->request->post('captcha');
//            }
//            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
//            $result = $validate->check($data);
            //极验
            if (Config::get('fastadmin.login_captcha'))
            {
                $data = $this->request->post();
            }
            $validate = new \addons\geet\controller\Index();
            $result = $validate->check($data);

            if (!$result)
            {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login'));
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($result === true)
            {
                Hook::listen("admin_login_after", $this->request);
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            }
            else
            {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin())
        {
            $this->redirect($url);
        }
        $background = Config::get('fastadmin.login_background');
        $background = stripos($background, 'http')===0 ? $background : config('site.cdnurl') . $background;
        $this->view->assign('background', $background);
        $this->view->assign('title', __('Login'));
        Hook::listen("admin_login_init", $this->request);
        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);
        $this->success(__('Logout successful'), 'index/login');
    }

}
