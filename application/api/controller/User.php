<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Config;
use think\Cookie;
use think\Db;
use think\Hook;
use think\Log;
use think\Session;
use think\Validate;
use tree\Tree2;

/**
 * 会员接口.
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'findpwd', 'changeemail', 'changemobile', 'third', 'syslogin'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
        $auth = $this->auth;
        $this->model = new \app\common\model\User();
        //监听注册登录注销的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 30 * 86400;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
            session('uid', $user->id);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {
//            Cookie::set('uid', $user->id);
//            Cookie::set('token', $auth->getToken());
        });
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
            session('uid', null);
        });
    }

    /**
     * 会员中心.
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录.
     *
     * @param string $username 账号
     * @param string $password 密码
     * @param string $captcha  验证码
     */
    public function login()
    {
        $account = $this->request->request('username');
        $password = $this->request->request('password');
        $captcha = $this->request->request('captcha');
        $keeplogin = (int) $this->request->request('keeplogin');

        //防止重复提交
        $c = md5(serialize($this->request->request()));
        $find = session($c);
        if ($find) {
            if ($find['expire'] + 2 - time() >= 0) {
                return false;
            }
        }
        session($c, array('expire' => time()));
//        if (!Sms::check($account, $captcha, 'login')) {
//            $this->error(__('验证码不正确'));
//        }

//        自带验证
//        $captcha = $this->request->request('captcha');
//        $rule = [
//            'captcha' => 'require|captcha',
//        ];
//        $msg = [
//            'captcha.require' => '请输入验证码',
//            'captcha.captcha' => '验证码错误',
//        ];
//        $data = [
//            'captcha' => $captcha,
//        ];
//        $validate = new Validate($rule, $msg);
//        $result = $validate->check($data);
        //极验

        $parm = $this->request->post();
        $validate = new \addons\geet\controller\Index();
        $result = $validate->check($parm);

        if (!$result) {
            $this->error(__($validate->getError()), 123);

            return false;
        }
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'), 123);
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            \app\common\model\Sms::where(['mobile' => $account, 'event' => 'login'])
                ->order('id', 'DESC')->delete();
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success('', $data, url('index/index/index1'));
        } else {
            $this->error($this->auth->getError(), 123);
        }
    }

    /**
     * 手机验证码登录.
     *
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1[3-9]\d{9}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 前台注册会员.
     *
     * @param string $username    用户名
     * @param string $password    登录密码
     * @param string $password2   支付密码
     * @param string $mobile      手机号
     * @param string $captcha     手机验证码
     * @param string $tjuser      推荐人
     * @param string $weixin      微信
     * @param string $alipay      支付宝
     * @param string $idcard      身份证
     * @param string $banknum     银行卡
     * @param string $bankname    开户行
     * @param string $bankusename 开户名
     */
    public function register()
    {
        $params = $this->request->request();
        $mobile = $params['mobile'];
        $captcha = $params['captcha'];
        $params['username'] = $params['mobile'];

        $validate = new \app\index\validate\User();
        $result = $validate->scene('reg')->check($params);
        if (!$result) {
            $this->error(__($validate->getError()));
        }
        $re = Sms::check($mobile, $captcha, 'register');
        $re = 1;
        if ($re) {
            $params['password'] = authcode($params['password']);
            $params['password2'] = authcode($params['password2']);
            $params['createtime'] = time();
            $params['joinip'] = request()->ip();
            if ($params['twon']) {
                $addr = explode(' ', $params['twon']);
                $params['province'] = $addr[0];
                $params['city'] = $addr[1];
                $params['area'] = $addr[2];
            }
            if ($params['tjuser'] != '') {
                $tjuser = $this->model->getByUsername($params['tjuser']);
                $params['tjid'] = $tjuser['id'];
                $params['ztlevel'] = $tjuser['ztlevel'] + 1;
                $params['tpath'] = $tjuser['tpath'].','.$tjuser['id'];
            } else {
                $params['tpath'] = 0;
            }
            $params['tgno'] = md5(time().$params['username']);
            $params['walladress'] = md5(time().$params['mobile']);
            $ret = Db::name('user')->strict(false)->insert($params);
            if ($ret) {
                $this->success(__('Sign up successful'), '', url('index/user/login'));
            } else {
                $this->error('注册失败');
            }
        } else {
            $this->error('验证码不正确');
        }
    }

    /**
     * 注销登录.
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success('', '', url('index/user/login'));
    }

    /**
     * 修改会员个人信息.
     *
     * @param string $avatar   头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio      个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $params = $this->request->request();
        $password = input('post.password');
        if (authcode($user['password2'], 'DECODE') != $password) {
            $this->error('您输入的交易密码错误');
        }
        $validate = new \app\index\validate\User();
        $result = $validate->scene('edit2')->check($params);
        if (!$result) {
            $this->error(__($validate->getError()));
        }
        $user->allowField(true)->save($params, ['id' => $user->id]);
        $this->success('修改成功', 789);
    }

    /**
     * imtoken 绑定.
     */
    public function imtoken()
    {
        $user = $this->auth->getUser();
        $params = $this->request->request();
        $password = input('post.password');
        if (authcode($user['password2'], 'DECODE') != $password) {
            $this->error('您输入的交易密码错误');
        }
        $user->allowField(true)->save($params, ['id' => $user->id]);
        $this->success('修改成功', 789);
    }

    /**
     * 修改会员收货信息.
     */
    public function profile2()
    {
        $user = $this->auth->getUser();
        $params = $this->request->request();
        if (empty($params['shouphone'])) {
            $this->error('请输入手机号');
        }
        if (!Validate::regex($params['shouphone'], "^1[3-9]\d{9}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $user->allowField(true)->save($params, ['id' => $user->id]);
        $this->success('修改成功', 789);
    }

    /**
     * 修改登录密码
     *
     * @param string $oldpassword   原密码
     * @param string $newpassword   新密码
     * @param string $renewpassword 新密码确认
     */
    public function changepwd()
    {
        $oldpassword = $this->request->request('oldpassword');
        $newpassword = $this->request->request('newpassword');
        $renewpassword = $this->request->request('renewpassword');
        $token = $this->request->request('__token__');
        $rule = [
            'oldpassword' => 'require|length:5,30',
            'newpassword' => 'require|length:5,30',
            'renewpassword' => 'require|length:5,30|confirm:newpassword',
            '__token__' => 'token',
        ];

        $msg = [
        ];
        $data = [
            'oldpassword' => $oldpassword,
            'newpassword' => $newpassword,
            'renewpassword' => $renewpassword,
            '__token__' => $token,
        ];
        $field = [
            'oldpassword' => __('Old password'),
            'newpassword' => __('New password'),
            'renewpassword' => __('Renew password'),
        ];
        $validate = new Validate($rule, $msg, $field);
        $result = $validate->check($data);
        if (!$result) {
            $this->error(__($validate->getError()));

            return false;
        }

        $ret = $this->auth->changepwd($newpassword, $oldpassword);
        if ($ret) {
            $this->success(__('Reset password successful'), '', url('index/user/login'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 修改支付密码
     *
     * @param string $oldpassword   原密码
     * @param string $newpassword   新密码
     * @param string $renewpassword 新密码确认
     */
    public function changepwd2()
    {
        $oldpassword = $this->request->request('oldpassword');
        $newpassword = $this->request->request('newpassword');
        $renewpassword = $this->request->request('renewpassword');
        $token = $this->request->request('__token__');
        $rule = [
            'oldpassword' => 'require|length:5,30',
            'newpassword' => 'require|length:5,30',
            'renewpassword' => 'require|length:5,30|confirm:newpassword',
            '__token__' => 'token',
        ];

        $msg = [
        ];
        $data = [
            'oldpassword' => $oldpassword,
            'newpassword' => $newpassword,
            'renewpassword' => $renewpassword,
            '__token__' => $token,
        ];
        $field = [
            'oldpassword' => __('Old password'),
            'newpassword' => __('New password'),
            'renewpassword' => __('Renew password'),
        ];
        $validate = new Validate($rule, $msg, $field);
        $result = $validate->check($data);
        if (!$result) {
            $this->error(__($validate->getError()));

            return false;
        }

        $ret = $this->auth->changepwd2($newpassword, $oldpassword);
        if ($ret) {
            $this->success(__('Reset password successful'), '', url('user/login'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 找回 修改登录密码
     *
     * @param string $newpassword   新密码
     * @param string $renewpassword 新密码确认
     */
    public function findpwd()
    {
        $mobile = $this->request->request('mobile');
        $type = $this->request->request('type');
        $newpassword = $this->request->request('newpassword');
        $renewpassword = $this->request->request('renewpassword');
        $rule = [
            'newpassword' => 'require|length:5,30',
            'renewpassword' => 'require|length:5,30|confirm:newpassword',
        ];

        $msg = [
            'newpassword.require' => '请输入新密码',
            'renewpassword.confirm' => '两次密码输入不一致，请重新输入',
        ];
        $data = [
            'newpassword' => $newpassword,
            'renewpassword' => $renewpassword,
        ];

        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->error(__($validate->getError()));

            return false;
        }
        $newpassword = authcode($newpassword);
        if ($type == 1) {
            $ret = Db::name('user')->where('mobile', $mobile)->setField('password', $newpassword);
        } else {
            $ret = Db::name('user')->where('mobile', $mobile)->setField('password2', $newpassword);
        }
        if ($ret) {
            $this->success(__('Reset password successful'), '', url('index/user/login'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 修改邮箱.
     *
     * @param string $email   邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->request('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, 'email')) {
            $this->error(__('Email is incorrect'));
        }
        if ($this->model->where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号.
     *
     * @param string $email   手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1[3-9]\d{9}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if ($this->model->where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录.
     *
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->request('platform');
        $code = $this->request->request('code');
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo' => $this->auth->getUserinfo(),
                    'thirdinfo' => $result,
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function resetpwd()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        $type = $this->request->request('type');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        //防止重复提交
        $a = microtime(true) * 10000;
        $c = md5(serialize($this->request->request()));
        $find = session($c);
        if ($find) {
            //50微妙
            if ($find['expire'] + 50 - $a >= 0) {
                return false;
            }
        }
        session($c, array('expire' => $a));
        $rshy = $this->model->where(array('mobile' => $mobile))->find();
        if (!$rshy) {
            $this->error('您要找回的账号不存在！');
        }

        if (Sms::check($mobile, $captcha, 'resetpwd')) {
            $this->success('验证通过，请输入新密码', '', url('index/user/changepwd', ['mobile' => $mobile, 'type' => $type]));
//            $opwd = rand(11111, 99999);
//            $tpwd = rand(11111, 99999);
//            unset($uparr);
//            $uparr['password'] = authcode($opwd);
//            $uparr['password2'] = authcode($tpwd);
//            $rs = $this->model->where(array('id' => $rshy['id']))->setField($uparr);
//            if ($rs) {
//                if (Sms::send($mobile, 'sms5', 'getpwd', $opwd, $tpwd)) {
//                    $data['expire']=time();
//                    session('check',$data);
//                    $this->success('新的密码已发送到您的手机，请及时修改密码！');
//                } else {
//                    $this->error('密码找回失败！');
//                }
//            } else {
//                $this->error('密码找回失败！');
//            }
        } else {
            $this->error(__('验证码不正确'));
        }
    }

    /**
     * 重置支付密码
     *
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function resetpwd2()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        $rshy = $this->model->where(array('mobile' => $mobile))->find();
        if (!$rshy) {
            $this->error('您要找回的账号不存在！');
        }
        //防止重复提交
        $find = session('check');
        if ($find) {
            if ($find['expire'] + 30 - time() >= 0) {
                Log::info('防止重复提交');

                return false;
            }
        }
        if (Sms::check($mobile, $captcha, 'resetpwd')) {
            $opwd = rand(11111, 99999);
            $tpwd = rand(11111, 99999);
            unset($uparr);
//            $uparr['password'] = authcode($opwd);
            $uparr['password2'] = authcode($tpwd);
            $rs = $this->model->where(array('id' => $rshy['id']))->setField($uparr);
            if ($rs) {
                if (Sms::send($mobile, 'sms6', 'getpwd', $tpwd)) {
                    $data['expire'] = time();
                    session('check', $data);
                    $this->success('新的密码已发送到您的手机，请及时修改密码！');
                } else {
                    $this->error('密码找回失败！');
                }
            } else {
                $this->error('密码找回失败！');
            }
        } else {
            $this->error(__('验证码不正确'));
        }
    }

    /**
     * 获取用户名.
     */
    public function getrname()
    {
        $username = input('username');
        $name = $this->model->getByUsername($username);
        if (empty($name)) {
            $this->error('未找到用户');
        }
        $this->success($name['realname']);
    }

    /**
     * 根据钱包获取用户名.
     */
    public function getrname2()
    {
        $username = trim(input('username'));
        $name = $this->model->getByWalladress($username);
        if (empty($name)) {
            $this->error('未找到用户');
        }
        $this->success($name['realname']);
    }

    /**
     * 获取二维码
     *
     * @param string $link 链接
     *
     * @throws \Endroid\QrCode\Exceptions\DataDoesntExistsException
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionFailedException
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionUnknownException
     * @throws \Endroid\QrCode\Exceptions\ImageTypeInvalidException
     */
    public function sendlink()
    {
        $myset = config('site');
        $mylink = $this->request->get('link', '127.0.0.1');
        $mylink = 'http://'.$mylink;
        $qrCode = new \Endroid\QrCode\QrCode();
        $qrCode
            ->setText($mylink)
            ->setSize(300)
            ->setPadding(15)
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255])
            ->setLogoSize(50)
            ->setLabelFontPath(ROOT_PATH.'public/assets/fonts/fzltxh.ttf')
//            ->setLabel($mylink)
            ->setLabelFontSize(14)
            ->setLabelHalign(0)
            ->setLabelValign(0)
            ->setImageType($qrCode::IMAGE_TYPE_PNG);

        $qrCode->setLogo(ROOT_PATH.'public'.$myset['codelogo']);

        //也可以直接使用render方法输出结果
        $qrCode->render();
    }

    /**
     * 更换头像.
     */
    public function changeimg()
    {
        $url = $this->request->request('img');
        $user = $this->auth->getUser();
        if ($url) {
            $re = $user->save(['avatar' => $url]);
            if ($re) {
                $this->success('修改成功', '', url('mobile/index/index1'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $this->error('参数错误');
        }
    }

    /**
     * 更换支付宝付款码
     */
    public function upalipay()
    {
        $url = $this->request->request('img');
        $user = $this->auth->getUser();
        if ($url) {
            $re = $user->save(['alipayimage' => $url]);
            if ($re) {
                $this->success('修改成功', '');
//                $this->success('修改成功', '', url('mobile/index/index1'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $this->error('参数错误');
        }
    }

    /**
     * 更换微信付款码
     */
    public function upweixin()
    {
        $url = $this->request->request('img');
        $user = $this->auth->getUser();
        if ($url) {
            $re = $user->save(['weixinimgage' => $url]);
            if ($re) {
                $this->success('修改成功', '');
//                $this->success('修改成功', '', url('mobile/index/index1'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $this->error('参数错误');
        }
    }

    /**
     *手续费计算.
     */
    public function getfee()
    {
        $price = input('price');
        $number = input('number');
        $type = input('type');
        $user = $this->auth->getUserinfo();
        $myset = config('site');
        $wallone = $user['wall1'];
        $walltwo = $user['wall3'];
        $all = $price * $number;
        if (!$price) {
            $this->error('请输入价格');
        }
        if (!$number) {
            $this->error('请输入购买数量');
        }
        if ($type == 'buy') {
            $fee = round($all * $myset['buyfee'] * 0.01, 2);
//            $max = $walltwo / ($myset['buyfee'] * 0.01 + 1) / $price;
            $max = $myset['maxbuysl'];
            $trade = $all * ($myset['buyfee'] * 0.01 + 1);
//            if ($trade > $walltwo) {
//                $this->error('额度不足');
//            }
            if ($number > $max) {
                $this->error('购买数量超过最大购买');
            }
        } else {
            $fee = ceil($number * $myset['salefee'] * 0.01);
            $max = $wallone / ($myset['salefee'] * 0.01 + 1);
            $trade = $number / ($myset['salefee'] * 0.01 + 1) * $price;
            if ($number > $max) {
                $this->error('卖出数量超过最大卖出');
            }
        }
        $data['fee'] = $fee;
        $data['max'] = floor($max);
        $data['trade'] = round($trade, 2);

        return json($data);
    }

    /**
     * 会员激活.
     */
    public function jihuo()
    {
        $userid = input('id');
        $myset = config('site');
        $rshy = $this->auth->getUserinfo();
        $rsuser = $this->model->where(array('id' => $userid))->find();
        if (!$rsuser || $rsuser['jointime'] > 0) {
            $this->error('会员不存在或已激活！');
        }
        if ($rshy['wall8'] < $myset['jhprice']) {
            $this->error('激活码数量不足！');
        }
        $upary['jointime'] = time();
        if ($this->model->where(array('id' => $rsuser['id']))->setField($upary) === false) {
            $this->error('激活失败！');
        }
        caiwu($rshy['id'], -$myset['jhprice'], 10, 'wall8', '激活会员');
        update_user_tui($userid, 1);
        $this->success('激活成功', '', url('mobile/index/index1'));
    }

    /**
     * 抢激活.
     */
    public function jhm()
    {
        $info = Db::table('fa_config')->where('name', 'jhnum')->lock(true)->find();
        $jhtime = config('site.jhtime');   //每日激活时间
        $jhuid = config('site.jhuser');   //激活uid限制
        $jhprice = config('site.jhprice');   //激活需激活币
        $now = date('H');
        if ($now < $jhtime) {
            $this->error('激活开放时间，每日'.$jhtime.'点');
        }
        if ((int) $info['value'] == 0) {
            $this->error('很遗憾，今日激活名额已满，请明日再试！每天抢激活时间'.$jhtime.'点');
        }
        $rshy = $this->auth->getUserinfo();
        if ($rshy['id'] < $jhuid['start'] || $rshy['id'] > $jhuid['end']) {
            $this->error('不在激活范围内uid'.$rshy['id']);
        }
        if ($rshy['jointime'] > 0) {
            $this->error('会员已经激活！');
        }
        if ($rshy['wall8'] < $jhprice) {
            $this->error('激活码不足');
        }
        // 会员激活
        $upary['jointime'] = time();
        if (Db::table('fa_user')->where(array('id' => $rshy['id']))->setField($upary)) {
            caiwu($rshy['id'], -$jhprice, 10, 'wall8', '激活会员'.$rshy['username']);
            $jhm = (int) $info['value'] - 1;
            $re = Db::table('fa_config')->where('name', 'jhnum')->setField('value', $jhm);

            $this->success('恭喜，您已成功激活！', '', url('mobile/index/index1'));
        }
    }

    /**
     * 会员反馈.
     */
    public function sendmsg()
    {
        $data = input('post.');
        $title = $data['title'];
        $content = $data['content'];
        if (empty($title) || empty($content)) {
            $this->error('请输入标题和内容');
        }
        $user = $this->auth->getUserinfo();
        $data['uid'] = $user['id'];
        $data['username'] = $user['username'];
        $data['addtime'] = time();
        $rs = Db::name('message')->strict(true)->insert($data);
        if ($rs) {
            $this->success('上传成功,请等待平台处理!', '', url('mobile/index/index1'));
        } else {
            $this->error('上传失败！');
        }
    }

    /**
     * 树形图.
     *
     * @return \think\response\Json
     */
    public function mytree()
    {
        $user = $this->auth->getUserinfo();
        $myid = $user['id'];
        $map['id|username|mobile'] = $myid;
        $info = $this->model->where($map)->order('id desc')->find();

        $categories = $this->model->select();

        $tree = new Tree2();

        $newCategories = [];

        foreach ($categories as $item) {
            $jihuo = $item['jointime'] > 0 ? '已激活' : '未激活';
            $dongjie = $item['status'] != '1' ? '已冻结' : '正常';
            $item['name'] = '  等级:'.$item['level'].'  用户名：'.$item['username'].'  用户id：'.$item['id'].'  团队人数:'.$item['tdnum'].'  直推人数:'.$item['ztnum'].'-----状态：'.$dongjie.$jihuo;
            array_push($newCategories, $item);
        }

        if ($info) {
            $myid = $info['id'];
            $tree->init($newCategories);
            $treeStr = $tree->getTreeArray2($myid);
            $name = '第1层--'.'  等级:'.$info['level'].'  用户名：'.$info['username'].'  用户id：'.$info['id'].'  团队人数:'.$info['tdnum'].'  直推人数:'.$info['ztnum'];
            $returnArray[] = array('name' => $name, 'children' => $treeStr);

            return json($returnArray);
        } else {
            $myid = 0;
            $tree->init($newCategories);
            $treeStr = $tree->getTreeArray2($myid);
            $returnArray[] = array('name' => '全体', 'children' => $treeStr);

            return json($returnArray);
        }
    }

    /**
     * 一键登陆.
     */
    public function syslogin()
    {
        $id = input('uid');
        //直接登录会员
        $re = $this->auth->direct($id);
        if ($re) {
            header('Location: /index/index/index');
        }
    }

    /**
     * 申请代理.
     */
    public function agent()
    {
        $rshy = $this->auth->getUserinfo();
        $level_id = input('level_id');

        if ($rshy['jointime'] == 0) {
            $this->error('请激活账号再操作！', '', '');
        }
        if ($rshy['realname'] == '' || $rshy['alipay'] == '') {
            $this->error('请先进行身份认证！', '', url('index/user/realname'));
        }

        if ($level_id) {
            $level = Db::name('user_level')->where('level_id', $level_id)->find();
            $this->model->where(array('id' => $rshy['id']))->setField(['agent' => $level_id, 'agent_name' => $level['level_name']]);
        }
        $this->success('提交成功，审核中', '', url('mobile/index/index1'));
    }

    /**
     * 申请开店.
     */
    public function openshop()
    {
        $rshy = $this->auth->getUserinfo();
        $shopname = input('shopname');
        $shopcate = input('shopcate');
        $myset = \config('site');
//        if ($rshy['slrate'] < $myset['sellsl']) {
//            $this->error('您的算力少于' . $myset['sellsl'] . ', 暂不接受入驻');
//        }
        if ($rshy['jointime'] == 0) {
            $this->error('请激活账号再操作！', '', '');
        }
        if ($rshy['issm'] !== '1') {
            $this->error('请先进行身份认证！', '', url('index/user/realname'));
        }
        if ($shopname && $shopcate) {
            $this->model->where(array('id' => $rshy['id']))->setField(['shopcate' => $shopcate, 'shopname' => $shopname, 'shop_open' => 2]);
            $this->success('提交成功，审核中', 789);
        } else {
            $this->error('请填写资料！', '', '');
        }
    }

    /**
     * 二级密码验证
     */
    public function seccodedo()
    {
        $pass2 = input('pass2');
        $rshy = $this->auth->getUserinfo();
        if (authcode($rshy['password2'], 'DECODE') == $pass2) {
            $this->success('', 1);
        } else {
            $this->error('支付密码错误');
        }
    }

    /**
     * 实名认证 app.
     */
    public function Verified()
    {
        $data = input();
        //防止重复提交
        $a = microtime(true) * 10000;
        $c = md5(serialize($this->request->request()));
        $find = session($c);
        if ($find) {
            //5.4秒
            if ($find['expire'] + 54500 - $a >= 0) {
                return false;
            }
        }
        session($c, array('expire' => $a));

        $data_c['alipay'] = $data['alipay'];
        $data_c['weixin'] = $data['weixin'];
        $data_c['attribution'] = $data['attribution'];
        $data_c['bankusename'] = $data['bankusename'];
        $data_c['bankname'] = $data['bankname'];
        $data_c['banknum'] = $data['banknum']; //银行卡号
        $data_c['mobile'] = $data['mobile']; //预留手机号
        $data_c['realname'] = $data['realname']; //真实姓名
        $data_c['idcard'] = $data['idcard']; //身份证号
        $realname = $data['realname']; //真实姓名
        $idnum = $data['idcard']; //身份证号
        $cardno = $data['banknum']; //银行卡号
        $mobile = $data['mobile']; //预留手机号
        $captcha = $data['captcha']; //验证码
        $url = 'http://'.$_SERVER['HTTP_HOST'];
        $rshy = $this->auth->getUserinfo();
        $userid = $rshy['id']; //当前用户id
//        $idCardImage=$data['idcard_image'];
//        $photo=$data['photo'];
//        $urls = controller('common')->upload2();
//        $idCardImage =$urls['idcard_image'];  //身份证
//        $photo = $urls['photo']; //照片
//        $idCardImage = request()->file('idcard_image');//身份证
//        $photo = request()->file('photo');//照片
        if (empty($data['alipay']) || empty($data['weixin']) || empty($data['attribution']) || empty($data['bankusename']) || empty($data['banknum']) || empty($data['mobile']) || empty($data['realname']) || empty($data['idcard']) || empty($data['captcha'])) {
            $this->error(__('提交信息不完善'));
        }
        if (!Sms::check($mobile, $captcha, 'realname')) {
            $this->error(__('验证码不正确'));
        }
        if ($rshy['issm'] == '1') {
            $this->error(__('已实名，请勿重复提交'));
        }

//            if ($idCardImage) {
        // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
//                $idCardImage = $url . $idCardImage;
//                $photo = $url .$photo;
//                $re = face($idCardImage, $photo);
//                log::info('realname---');
//                log::info($re);
//                if ($re) {
//                    if ($re['code'] != '200000') {
//                        $this->error($re['message']);
//                    }
//                    if ($re['data']['livingFaceData']['code'] != 0) {
//                        $this->error('请上传真实照片验证');
//                    }
//                    if ($re['data']['ocrIdCardData']['code'] != 0) {
//                        $this->error('请上传真实照片验证');
//                    }
//                    if ($re['data']['ocrIdCardData']['cardNum'] == '') {
//                        $this->error($re['data']['remark']);
//                    }
//                    if ($re['data']['ocrIdCardData']['cardNum'] != $data['idcard']) {
//                        $this->error('身份证号与上传身份证不符,请检查后提交');
//                    }
//                    if(isset($re['data']['faceMatchData']['score']) ){
//                        if ( $re['data']['faceMatchData']['score'] < 87) {
//                            $this->error('您的照片与身份证不符,请重新上传');
//                        }
//                    }else{
//                        $this->error('您的照片与身份证不符,请重新上传');
//                    }

        $bankcard = bankcard($realname, $idnum, $cardno, $mobile);
        log::info('realname2---');
        log::info($bankcard);
        if ($bankcard['data']['result'] != 01) {
            $this->error('您的银行卡信息与真实信息不一致,请重新验证');
        }
        $data_c['issm'] = 1;
        $data_c['jointime'] = time();   //激活
        $user = Db::name('user')->where('id', $userid)->update($data_c);
//                    if ($user['mobile'] != $data['mobile']) {
//                        $this->error('预留手机号与注册手机号不符,请重新输入');
//                    }
        if ($user) {
            //送矿机
            update_user_tui($rshy['id'], 1);
//                        addMine($rshy['id'], $rshy['username'], 1, 1);
            addMine($rshy['id'], $rshy['username'], 1, 1);
            //送额度
            caiwu($rshy['id'], 10, 8, 'wall7', '实名奖励');
            Db::name('user')->where('id', $rshy['tjid'])->setInc('lmccj', 1); //增加抽奖次数
            $this->success('您已完成实名认证,感谢您的配合', '', url('index/user/center'));
        }
//                }
//            } else {
//                // 上传失败获取错误信息
//                $this->error('网络错误,图片上传失败');
//            }
    }

//    /**
//     * 实名认证
//     */
//    public function Verified()
//    {
//        $data = input();
//        $data_c['alipay'] = $data['alipay'];
//        $data_c['weixin'] = $data['weixin'];
//        $data_c['attribution'] = $data['attribution'];
//        $data_c['bankusename'] = $data['bankusename'];
//        $data_c['bankname'] = $data['bankname'];
//        $data_c['banknum'] = $data['banknum'];//银行卡号
//        $data_c['mobile'] = $data['mobile'];//预留手机号
//        $data_c['realname'] = $data['realname'];//真实姓名
//        $data_c['idcard'] = $data['idcard'];//身份证号
//        $realname = $data['realname'];//真实姓名
//        $idnum = $data['idcard'];//身份证号
//        $cardno = $data['banknum'];//银行卡号
//        $mobile = $data['mobile'];//预留手机号
//        $captcha = $data['captcha'];//验证码
//        $url = 'http://' . $_SERVER['HTTP_HOST'];
//        $rshy = $this->auth->getUserinfo();
//        $userid = $rshy['id'];//当前用户id
//
//        $urls = controller('common')->upload2();
//        $idCardImage =$urls['idcard_image'];  //身份证
//        $photo = $urls['photo']; //照片
    ////        $idCardImage = request()->file('idcard_image');//身份证
    ////        $photo = request()->file('photo');//照片
//        if (empty($idCardImage) || empty($photo) ||empty($data['alipay']) || empty($data['weixin']) || empty($data['attribution']) || empty($data['bankusename']) || empty($data['banknum']) || empty($data['mobile']) || empty($data['realname']) || empty($data['idcard']) || empty($data['captcha'])) {
//            $this->error(__('提交信息不完善'));
//        }
//        if (!Sms::check($mobile, $captcha, 'realname')) {
//            $this->error(__('验证码不正确'));
//        }
//        // 移动到框架应用根目录/public/uploads/ 目录下
//        if ($idCardImage) {
    ////            $idCardImage = $idCardImage->move(ROOT_PATH . 'public' . DS . 'uploads');
    ////            $photo = $photo->move(ROOT_PATH . 'public' . DS . 'uploads');
//            if ($idCardImage) {
//                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
//                $idCardImage = $url . $idCardImage;
//                $photo = $url .$photo;
//                $re = face($idCardImage, $photo);
//                log::info('realname---');
//                log::info($re);
//                if ($re) {
//                    if ($re['code'] != '200000') {
//                        $this->error($re['message']);
//                    }
//                    if ($re['data']['ocrIdCardData']['code'] != 0) {
//                        $this->error('请上传真实照片验证');
//                    }
//                    if ($re['data']['ocrIdCardData']['cardNum'] == '') {
//                        $this->error($re['data']['remark']);
//                    }
//                    if ($re['data']['ocrIdCardData']['cardNum'] != $data['idcard']) {
//                        $this->error('身份证号与上传身份证不符,请检查后提交');
//                    }
//                    if ($re['data']['livingFaceData']['score'] < 87) {
//                        $this->error('您的照片与身份证不符,请重新上传');
//                    }
//                    $bankcard = bankcard($realname, $idnum, $cardno, $mobile);
//                    log::info('realname2---');
//                    log::info($bankcard);
//                    if ($bankcard['data']['result'] != 01) {
//                        $this->error('您的银行卡信息与真实信息不一致,请重新验证');
//                    }
//                    $data_c['issm'] = 1;
//                    $data_c['jointime'] = time();   //激活
//                    $user = Db::name('user')->where('id', $userid)->update($data_c);
    ////                    if ($user['mobile'] != $data['mobile']) {
    ////                        $this->error('预留手机号与注册手机号不符,请重新输入');
    ////                    }
//                    if ($user) {
//                        //送矿机
//                        addMine($rshy['id'], $rshy['username'], 1, 1);
//                        addMine($rshy['id'], $rshy['username'], 1, 1);
//                        $this->success('您已完成实名认证,感谢您的配合', url('mobile/index/index1'));
//                    }
//                }
//            } else {
//                // 上传失败获取错误信息
//                $this->error('网络错误,图片上传失败');
//            }
//        }
//    }

    /**
     * 慈善捐助.
     */
    public function donation()
    {
        $data = input();
        $user = $this->auth->getUser();
        if (empty($data['amount'])) {
            $this->error('请输入捐助金额');
        }
        if ($data['amount'] <= 0) {
            $this->error('请输入正确金额');
        }
        if ($data['amount'] > $user['wall2']) {
            $this->error('余额不足');
        }
        $price = abs($data['amount']);
        $is_true = caiwu($user['id'], -$price, 14, 'wall2', '慈善捐助');
        if ($is_true) {
            Db::name('donation')->where('id', $data['pid'])->setInc('amount_donated', $data['amount']);
            Db::name('donation')->where('id', $data['pid'])->setInc('number', 1);

            //用户爱心值增加$data['amount']
            Db::name('user')->where('id', $user['id'])->update(['love_number' => $user['love_number'] + $data['amount']]);
            //用户捐助额增加$data['amount']
            Db::name('user')->where('id', $user['id'])->update(['contribution_amount' => $user['contribution_amount'] + $data['amount']]);
            //用户信用值增加$data['amount']
            Db::name('user')->where('id', $user['id'])->update(['star' => $user['star'] + $data['amount']]);

            $this->success('捐助成功');
        }
    }

    //转盘抽奖
    public function luckydraw()
    {
        $user = $this->auth->getUserinfo();
        if ($_POST != '') {
            $awards = $_POST['awards'];

            //抽奖记录
            $api = db('zhuan_prize')->where('id', $awards)->find();

            $res['uid'] = $user['id'];
            $res['prize_id'] = $awards;
            $res['prize_name'] = $api['prize_name'];
            $res['time'] = time();
            db('zhuan_prizelog')->insert($res);
            if ($user['lmccj'] < 1) {
                $this->success('没有次数了');
            }
//            if ($user['Lottery'] <= 0) {
            $q = db('user')->where('id', $user['id'])->setDec('lmccj');
            if ($q) {
                caiwu($user['id'], -3, 17, 'wall2', '转盘扣除');
            }
//            } elseif ($prize) {
//                db('user')->where('id', $user['id'])->setDec('Lottery');
//            }

            //奖品发布
            if ($awards == 2) {
//                $money = 300;
//                caiwu($user['id'], $money, 17, 'wall1', '转盘奖励');
//                $this->success('恭喜获得' . $money . '源币');
//            } elseif ($awards == 4) {
//                $money = 10;
//                caiwu($user['id'], $money, 17, 'wall1', '转盘奖励');
//                $this->success('恭喜获得' . $money . '源币');
//            } elseif ($awards == 6) {
//                $money = 50;
//                caiwu($user['id'], $money, 17, 'wall1', '转盘奖励');
//                $this->success('恭喜获得' . $money . '源币');
            } elseif ($awards == 8) {
                $money = 5;
                caiwu($user['id'], $money, 17, 'wall1', '转盘奖励');
                $this->success('恭喜获得'.$money.'源币');
//            } elseif ($awards == 10) {
//                $money = 100;
//                caiwu($user['id'], $money, 17, 'wall1', '转盘奖励');
//                $this->success('恭喜获得' . $money . '源币');
//            } elseif ($awards == 3) {
//                addMine($user['id'], $user['username'], 4, $iszs = 1);
//                $this->success('恭喜获得' . $api['prize_name']);
//            } elseif ($awards == 7) {
//                addMine($user['id'], $user['username'], 5, $iszs = 1);
//                $this->success('恭喜获得' . $api['prize_name']);
//            } elseif ($awards == 9) {
//                addMine($user['id'], $user['username'], 3, $iszs = 1);
//                $this->success('恭喜获得' . $api['prize_name']);
            } else {
                $this->success('谢谢参与');
            }
        }
    }

    /**
     * 每日签到.
     */
    public function daybao()
    {
        $user = $this->auth->getUserinfo();

        $count = Db::name('caiwu')->where(['userid' => $user['id'], 'type' => 16])->whereTime('addtime', 'today')->count();

        if ($count > 0) {
            $this->error('今日已签到', 789);
        } else {
            $bouse = rand(1, 100) * 0.001;
            caiwu($user['id'], $bouse, 16, 'wall2', '签到奖励');
            $this->success('签到成功，奖励'.$bouse.'LMC');
        }
    }

    /**
     *  用户上传商品
     */
    public function uploadgoods()
    {
        $data = input('post.');
        if (isset($data['spec_type']) && is_array($data['spec_type'])) {
            foreach ($data['spec_type'] as $datum) {
                if ($datum == '') {
                    $this->error('请输入对应价格');
                }
            }
            $data['spec_type'] = json_encode($data['spec_type']);
        }

        $up = controller('common')->upload2();
        if (!isset($up['file1'])) {
            $this->error('商品图上传有误，商品图需要 从左到右上传');
        }
        $data['image'] = $up['file1'];
        $data['images'] = $up['file1'];
        if (isset($up['file2'])) {
            $data['images'] .= ','.$up['file2'];
        }
        if (isset($up['file3'])) {
            $data['images'] .= ','.$up['file3'];
        }
        $data['goods_content'] = $this->request->param('goods_content', '', 'trim');

        $re = Db::name('shop_goods')->strict(false)->insert($data);
        if ($re) {
            $this->success('操作成功', 789);
        } else {
            $this->error('操作失败,请稍后重试');
        }
    }

    /**
     *  用户编辑商品
     */
    public function editgoods()
    {
        $data = input('post.');
        $file = $this->request->file('file');
        if (!empty($file)) {
            $data['image'] = controller('common')->upload2();
        }
        $re = Db::name('shop_goods')->update($data);
        if ($re) {
            $this->success('操作成功', '', url('index/user/shopgoods'));
        } else {
            $this->error('操作失败,请稍后重试');
        }
    }

    /**
     *  用户下架商品
     */
    public function downgoods()
    {
        $id = input('gid');
        $info = Db::name('shop_goods')->find($id);
        if ($info['is_on_sale'] != '0') {
            $re = Db::name('shop_goods')->update(['goods_id' => $id, 'is_on_sale' => 0]);
            if ($re) {
                $this->success('操作成功', 321);
            } else {
                $this->error('操作失败,请稍后重试');
            }
        } else {
            $this->error('已经下架');
        }
    }

    /**
     *  用户上架商品
     */
    public function upgoods()
    {
        $id = input('gid');
        $info = Db::name('shop_goods')->find($id);
        if ($info['is_on_sale'] != '1') {
            $re = Db::name('shop_goods')->update(['goods_id' => $id, 'is_on_sale' => 1]);
            if ($re) {
                $this->success('操作成功', 321);
            } else {
                $this->error('已经上架');
            }
        } else {
            $this->error('已经上架');
        }
    }

    /**
     *  用户删除商品
     */
    public function delgoods()
    {
        $id = input('gid');
        $info = Db::name('shop_goods')->find($id);
        if ($info['is_on_sale'] != '1') {
            $re = Db::name('shop_goods')->where('goods_id', $id)->delete();
            if ($re) {
                $this->success('操作成功', 321);
            } else {
                $this->error('操作失败');
            }
        } else {
            $this->error('操作失败');
        }
    }

    /**
     *  用户发货.
     */
    public function fahuo()
    {
        $data = input('post.');
        $id = $data['id'];
        $info = Db::name('shop_order')->find($id);
        if ($info['shipping_num'] > 0) {
            $this->error('已发货，请勿重复操作');
        }
        if (empty($data['shipping_name'])) {
            $this->error('请输入物流名称');
        }
        if (empty($data['shipping_num'])) {
            $this->error('请输入快递单号');
        }
        $re = Db::name('shop_order')->where('order_id', $info['order_id'])->setField(['shipping_time' => time(), 'shipping_name' => $data['shipping_name'], 'shipping_num' => $data['shipping_num'], 'order_status' => '2']);

        if ($re) {
            $this->success('操作成功', 321);
        } else {
            $this->error('操作失败,请稍后重试');
        }
    }

    /**
     *  实体申请.
     */
    public function realshop()
    {
        $data = input('post.');
        $user = $this->auth->getUserinfo();
        $urls = controller('common')->upload2();
        $data['is_real'] = 2;
        $all = array_merge($data, $urls);
        if (empty($all['shopname']) || empty($all['shopcate']) || empty($all['realname']) || empty($all['idcard']) || empty($all['idcard_1']) || empty($all['idcard_2']) || empty($all['business']) || empty($all['business2'])) {
            $this->error('请完善信息再提交');
        }
        $re = Db::name('user')->where('id', $user['id'])->update($all);
        if ($re) {
            $this->success('操作成功', 789);
        } else {
            $this->error('操作失败,请稍后重试');
        }
    }

    /**
     *  图片更新 (头像 收款码).
     */
    public function updateimg()
    {
        $data = input('post.');
        $user = $this->auth->getUserinfo();
        $urls = controller('common')->upload2();
        $re = Db::name('user')->where('id', $user['id'])->update($urls);
        if ($re) {
            $this->success('', reset($urls));
        }
    }

    /**
     *  实名上传图片.
     */
    public function realimg()
    {
        $data = input();
        $urls = controller('common')->upload2();
        $this->success('', reset($urls));
    }
}
