<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 互助打款接口
 */
class Business2 extends Api
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
        $payimg = input('post.payimg');
        if (empty($payinfo)) {
            $this->error('请输入支付说明');
        }
        if (empty($payimg)) {
            $this->error('请选择图片再确认');
        }
        $ppmx2 = model('ppmx2');
        $rsmx = $ppmx2->get($ppid);
        if (!$rsmx || (int)$rsmx['status'] !== 0) {
            $this->error('订单已付款或不存在');
        }
        unset($upary);
        $upary['payinfo'] = $payinfo;
        $upary['pimg'] = input('payimg');
        $upary['status'] = 1;
        $upary['paytime'] = time();//上传凭证更新时间
        $rs = $ppmx2->where(array('ppid' => $rsmx['ppid']))->setField($upary);
        if ($rs) {
//            $content = "尊敬的会员{$rsmx['account1']}，您申请接受帮助的订单对方已经打款，请您及时登录系统处理！";
//            smscode($rsmx['userid1'], $content);

//            $this->success('上传成功,请等待确认!', U('Business/tgdk', array('tgid' => $rsmx['tgid'])));
            $this->success('上传成功,请等待确认!','', url('index/user/index'));
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
        $ppmx2 = model('ppmx2');
        $users = model('user');
        $myset = config('site');
        $rspp = $ppmx2->get($ppid);
        if (!$rspp || (int)$rspp['status'] !== 1) {
            $this->error('获取匹配失败，或已经确认收款','', url('index/user/index'));
        }
        $buyuid = $rspp['userid'];
        $saleuid = $rspp['userid1'];
        unset($upary);
        $upary['status'] = 2;
        $upary['confirmtime'] = time();
        $rs = $ppmx2->where('ppid', $rspp['ppid'])->setField($upary);

        if ($rs) {
            $users->where(array('id' => $buyuid))->setInc('tzprice', $rspp['total']);
            $users->where(array('id' => $buyuid))->setInc('futou', 1);     //互助次数
//            caiwu($buyuid, $rspp['number'], 2, 'wall3', '投资买入')

            $tg =  db('tgmx2')->where('tgid',$rspp['tgid'])->find();
            if($tg['status']=='5'){
                db('profit')->where('tgid',$rspp['tgid'])->delete();
                db('profit')->insert(['user_id' => $rspp['userid'], 'money' => $tg['number'], 'add_time' => time(), 'tgid' => $rspp['tgid'],'profit' =>$tg['number']*$tg['flrate']*0.01, 'type' => 1]);
                //分享人上级动态推荐奖
                dtj($rspp['userid'], $tg['number'],$rspp['tgid']);
                //级差奖
                jcj($rspp['userid'], $tg['number'],$rspp['tgid']);
            }else{
                db('profit')->insert(['user_id' => $rspp['userid'], 'money' => $rspp['number'], 'add_time' => time(), 'tgid' => $rspp['tgid']]);
            }
//            $pp = $ppmx2->get($ppid);
            //5小时内打款 诚信奖励
//            if (($pp['paytime'] - $pp['addtime']) <= $myset['cxtime'] * 3600) {
//                $profit = $rspp['number'] * $myset['cxrate'] * 0.01;
//                caiwu($buyuid, $profit, 9, 'wall4', '诚信奖励金');
//            }

//            $kk = $rspp['number'] * 10 * 0.01;
//            caiwu($buyuid, $kk, 9, 'wall4', '静态余额');     //静态

            //激活分享人有效期
//            $users->where(array('user_id' => $rspp['userid']))->setField(['jhstate' => 1, 'jh_time' => time()]);
            //更新分享人级别
            update_user_tui($rspp['userid']);

            $this->success('确认收款成功!','', url('index/user/index'));
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
        $ppmx2 = model('ppmx2');
        $rsmx = $ppmx2->get($ppid);
        if (!$rsmx) {
            $this->error('订单不存在');
        }
        unset($upary);
        $upary['tsinfo'] = $payinfo;
        $upary['status'] = 4;
        $upary['tstime'] = time();
        $upary['tsimg'] = input('touimg');
        $rs = $ppmx2->where(array('ppid' => $rsmx['ppid']))->setField($upary);
        if ($rs) {
            $this->success('上传成功,请等待平台处理!','', url('index/user/index'));
        } else {
            $this->error('上传失败！');
        }
    }


}
