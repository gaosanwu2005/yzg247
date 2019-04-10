<?php

namespace app\index\validate;

use think\Db;
use think\Validate;

class User extends Validate
{
    protected $regex = ['idcard' => '/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X|x)$/'];
    /**
     * 验证规则
     */
    protected $rule = [
        'username'    => 'require|length:6,30|checkunique:1,username',
        'password'    => 'require|length:6,30',
        'password2'   => 'require|length:6,30',
        'repassword'  => 'require|confirm:password',
        'repassword2'  => 'require|confirm:password2',
        'mobile'      => 'require|regex:/^1[3-9]\d{9}$/|checkunique:1,mobile',
        'idcard'      => 'require|regex:idcard|checkunique:1,idcard',
        'weixin'      => 'require|checkunique:1,weixin',
        'alipay'      => 'require|checkunique:1,alipay',
        'bankusename' => 'require',
        'bankname'    => 'require',
        'banknum'     => 'require|checkunique:1,banknum',
        'tjuser'      => 'require|checktjuser',
        'captcha'     => 'require',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'username.require'     => '用户名不能为空',
        'username.length'      => '用户名必须为6到30个字符',
        'password.require'     => '登录密码不能为空',
        'password.length'      => '登录密码必须为6到30个字符',
        'password2.require'    => '支付密码不能为空',
        'password2.length'     => '支付密码必须为6到30个字符',
        'repassword.confirm'   => '登陆密码不一致，请重新输入',
        'repassword2.confirm'   => '交易密码不一致，请重新输入',
        'repassword.require'     => '请输入确认密码',
        'repassword2.require'     => '请输入确认密码',
        'mobile.require'       => '手机不能为空',
        'mobile.regex'         => '手机号错误',
        'shouphone.regex'         => '手机号错误',
        'idcard.require'       => '身份证不能为空',
        'idcard.regex'         => '身份证错误',
        'weixin.require'       => '微信不能为空',
        'alipay.require'       => '支付宝不能为空',
        'bankusename.require'  => '开户名不能为空',
        'banknum.require'      => '银行卡不能为空',
        'bankname.require'     => '开户行不能为空',
        'tjuser.require'   => '推荐人不能为空',
        'tjuser.checktjuser'   => '推荐人不存在',
        'captcha.require'      => '验证码不能为空',
        'username.checkunique' => '用户名已存在',
        'mobile.checkunique'   => '手机已存在',
        'idcard.checkunique'   => '身份证已存在',
        'weixin.checkunique'   => '微信已存在',
        'alipay.checkunique'   => '支付宝已存在',
        'banknum.checkunique'  => '银行卡已存在',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => ['idcard', 'weixin', 'alipay', 'bankusename', 'bankname', 'banknum'],
        'edit2' => ['idcard'=>'require|regex:idcard',
                    'weixin'      => 'require',
                    'alipay'      => 'require', 
                    'bankname'    => 'require',
                    'banknum'     => 'require'],
        'edit3' => [ 'shouphone'=> 'require|regex:/^1[3-9]\d{9}$/'],
        'reg' =>['mobile', 'tjuser', 'password', 'password2', 'repassword', 'repassword2'],
    ];

    // 验证数量
    protected function checkunique($value, $rule, $data)
    {
        $info = explode(',', $rule);
        $map[$info[1]] = $value;
        $num = Db::name('user')->where($map)->count();
        if ($num >= $info[0]) {
            return false;
        } else {
            return true;
        }
    }

    protected function checktjuser($tjuser)
    {
        if ($tjuser) {
            $rs = Db::name('user')->where(array('username' => $tjuser, 'status' => 1))->find();
            if ($rs) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
