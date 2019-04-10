<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Sms as Smslib;
use think\Db;

/**
 * 打款接口
 */
class Business extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 付款
     * @throws \think\exception\DbException
     */
    public function paydo()
    {
        $ppid = input('post.ppid');
        $payinfo = input('post.payinfo');
        if (empty($payinfo)) {
            $this->error('请输入支付说明');
        }
        $ppmx = model('ppmx');
        $rsmx = $ppmx->get($ppid);
        if (!$rsmx || (int)$rsmx['status'] !== 0) {
            $this->error('订单已付款或不存在');
        }
        unset($upary);
        $upary['payinfo'] = $payinfo;
        $upary['pimg'] = input('payimg');
        $upary['status'] = 1;
        $upary['paytime'] = time();//上传凭证更新时间
        $rs = $ppmx->where(array('ppid' => $rsmx['ppid']))->setField($upary);
        if ($rs) {
//            $content = "尊敬的会员{$rsmx['account1']}，您申请接受帮助的订单对方已经打款，请您及时登录系统处理！";
//            smscode($rsmx['userid1'], $content);
            $saleuser= Db::table('fa_user')->find($rsmx['userid1']);
            Smslib::jhsend($saleuser['mobile'], '7', 'paysus');
//            $this->success('上传成功,请等待确认!', U('Business/tgdk', array('tgid' => $rsmx['tgid'])));
            $this->success('上传成功,请等待确认!','', url('index/trade/index'));
        } else {
            $this->error('上传失败！');
        }
    }

    /**
     * 确认收款
     */
    public function confirm()
    {
        $ppid = input('get.ppid');   //匹配单id
        $ppmx = model('ppmx');
        $users = model('user');
        $myset = config('site');
        $rspp = $ppmx->get($ppid);
        if (!$rspp || (int)$rspp['status'] !== 1) {
            $this->error('获取匹配失败，或已经确认收款');
        }
        $buyuid = $rspp['userid'];
        $saleuid = $rspp['userid1'];
        unset($upary);
        $upary['status'] = 2;
        $upary['confirmtime'] = time();
        $rs = $ppmx->where('ppid', $rspp['ppid'])->setField($upary);

        if ($rs) {
            //增加买家WALL2
            caiwu($buyuid, $rspp['number'], 2, 'wall2', '投资买入');
            //增加买家交易额度
            caiwu($buyuid, $rspp['number']*1.7, 2, 'wall7', '投资买入增加授信额度');
            //减少卖家冻结
            $fee = round($rspp['number'] * $myset['salefee'] * 0.01, 4);
            $total = $rspp['number']+$fee;
            $users->where(array('id' => $saleuid))->setDec('freeze2', $total);
            //减少卖家授信额度冻结
            $users->where(array('id' => $saleuid))->setDec('freeze7', $rspp['number']);
            //减少卖家令牌冻结
            $v1=ceil($rspp['number']/50);
            $users->where(array('id' => $saleuid))->setDec('freezev1',$v1);

            //释放上轮静态余额
//             $users->where(array('user_id' => $tguser['user_id']))->setField('wallthree2', $tguser['wallthree']);
//            if () {
//                M('profit')->add(['user_id' => $rspp['userid'], 'money' => $rspp['number'], 'add_time' => time()]);
//                $users->where(array('user_id' => $rspp['userid']))->setInc('walltwo1', $rspp['number']);
//            }

//            $pp = $ppmx->get($ppid);
//            //5小时内打款 诚信奖励
//            if (($pp['paytime'] - $pp['addtime']) <= $myset['cxtime'] * 3600) {
//                $profit = $rspp['number'] * $myset['cxrate'] * 0.01;
//                caiwu($buyuid, $profit, 9, 'wall3', '诚信奖励金');
//            }
//
//            $kk = $rspp['number'] * 10 * 0.01;
//            caiwu($buyuid, $kk, 9, 'wall4', '静态余额');     //静态

            //激活分享人有效期
//            $users->where(array('user_id' => $rspp['userid']))->setField(['jhstate' => 1, 'jh_time' => time()]);
//            //更新分享人级别
//            update_user_tui($rspp['userid']);
//            //分享人上级动态推荐奖
//             dtj($rspp['userid'], $rspp['number']);
            $this->success('确认收款成功!','', url('index/trade/index'));
        }


    }

    /**
     * 投诉上传
     */
    public function shamtss()
    {
        $ppid = input('post.ppid');
        $payinfo = input('post.tsinfo');
        if (empty($payinfo)) {
            $this->error('请输入投诉说明');
        }
        $ppmx = model('ppmx');
        $rsmx = $ppmx->get($ppid);
        if (!$rsmx) {
            $this->error('订单不存在');
        }
        unset($upary);
        $upary['tsinfo'] = $payinfo;
        $upary['status'] = 4;
        $upary['tstime'] = time();
        $upary['tsimg'] = input('touimg');
        $rs = $ppmx->where(array('ppid' => $rsmx['ppid']))->setField($upary);
        if ($rs) {
            $this->success('上传成功,请等待平台处理!','', url('index/trade/index'));
        } else {
            $this->error('上传失败！');
        }
    }


}
