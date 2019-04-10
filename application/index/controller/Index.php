<?php

namespace app\index\controller;

use addons\epay\library\Service;
use app\common\controller\Frontend;
use think\Db;
use Yansongda\Pay\Pay;

class Index extends Frontend
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();

        $this->assign('debug', config('app_debug'));

        \config('default_ajax_return', 'html');
    }

    public function _empty()
    {
        return $this->fetch();
    }

    public function index()
    {
        $url = url('index/index1');
        $this->assign('url', $url);

        return $this->fetch();
    }

    //APP首页
    public function index1()
    {
        $announcement = Db::name('articale')->where('category_id=86')->order('id desc')->limit('1')->find(); //公告
        // $news = Db::name('articale')->where('switch', 1)->where('category_id=35')->order('id desc')->limit(5)->select();
        $favourite_goods = Db::name('shop_goods')->where(['is_recommend' => 1, 'is_on_sale' => 1])->order('goods_id DESC')->limit(20)->cache(true)->select(); //首页推荐商品
        $this->assign('favourite_goods', $favourite_goods);
        // $this->assign('news', $news);
        $this->assign('announcement', $announcement);

        return $this->fetch();
    }

    /**
     * 获取更多.
     *
     * @return mixed
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function newsajax()
    {
        $news = Db::name('articale')->where('switch', 1)->where('category_id=35')->order('id desc')->paginate(5);
        $this->assign('news', $news);

        return $this->fetch();
    }

    public function index2()
    {
        $announcement = Db::name('articale')->where('category_id=86')->order('id desc')->limit('1')->find(); //公告
        $news = Db::name('articale')->where('switch', 1)->where('category_id=35')->order('id desc')->limit(5)->select();
        $this->assign('news', $news);
        $this->assign('announcement', $announcement);

        return $this->fetch();
    }

    /**
     * 推广注册
     *Create by xiaoniu.
     */
    public function reg()
    {
        $tgno = input('get.tgno');
        if ($tgno == '') {
            $this->redirect('index/user/login');
        }
        $rshy = db('user')->where(array('tgno' => $tgno))->find();
        $url = url('user/reg')."?tjuser={$rshy['username']}";
        $this->assign('url', $url);

        return $this->fetch('index');
    }

    /**
     * 扫码转账
     *Create by xiaoniu.
     */
    public function pdzz()
    {
        $tgno = input('tgno');
        if ($tgno == '') {
            $this->redirect('index/user/login');
        }
        $rshy = db('user')->where(array('tgno' => $tgno))->find();
        $url = url('index/message/etc2lmc')."?tjuser={$rshy['walladress']}";
        $this->assign('url', $url);

        return $this->fetch('index');
    }

    /**
     * 扫码加好友
     *Create by xiaoniu.
     */
    public function addfriend()
    {
        $tgno = input('tgno');
        if ($tgno == '') {
            $this->redirect('index/user/login');
        }
        $info = db('user')->where(array('tgno' => $tgno))->find();
        if ($info) {
            $user = $this->auth->getUserinfo();
            $auids = json_decode($user['friends']);
            $buids = json_decode($info['friends']);
            if ($auids) {
                if (!in_array($info['id'], $auids)) {
                    $auids[] = $info['id']; //添加成员
                    db('user')->where('id', $user['id'])->setField('friends', json_encode($auids));
                }
            } else {
                $auids[] = $info['id']; //添加成员
                db('user')->where('id', $user['id'])->setField('friends', json_encode($auids));
            }
            if ($buids) {
                if (!in_array($user['id'], $buids)) {
                    $buids[] = $user['id']; //添加成员
                    db('user')->where('id', $info['id'])->setField('friends', json_encode($buids));
                }
            } else {
                $buids[] = $user['id']; //添加成员
                db('user')->where('id', $info['id'])->setField('friends', json_encode($buids));
            }

            $this->redirect('index/index/successinfo', array('payback' => '添加成功'));
        } else {
            $this->redirect('index/index/successinfo', array('payback' => '用户不存在'));
        }
    }

    /**
     * success
     *Create by xiaoniu.
     */
    public function successinfo()
    {
        $payback = input('payback', '');
        $url = url('user/successinfo')."?payback={$payback}";
        $this->assign('url', $url);

        return $this->fetch('index');
    }

    public function pay()
    {
        //创建支付对象
        $pay = Pay::alipay(Service::getConfig('alipay'));

        //构建订单信息
        $order = [
            'out_trade_no' => date('YmdHis'), //你的订单号
            'total_amount' => 1, //单位元
            'subject' => 'FastAdmin企业支付插件测试订单',
        ];

        //跳转或输出
        return $pay->web($order)->send();
    }
}
