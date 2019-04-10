<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;
use Think\Log;

class Trade extends Frontend
{
    protected $noNeedLogin = ['notify','payreturn'];
    protected $noNeedRight = ['*'];
    public function index()
    {
        $price = Db::name('tzrank')->order('rprice asc')->select();
        $this->assign('price', $price);

        $user = $this->auth->getUserinfo();
        $Tgmx = Db::name('Tgmx');
//        $Xymx = Db::name('Xymx');
        $Ppmx = Db::name('Ppmx');
        $myset = config('site');
        $buytrade = [];
        $saletrade = [];
        $map['userid'] = $user['id'];
        $map['a.status'] = '0';
        $map2['userid|userid1'] = $user['id'];
        $map2['status'] = ['in', ['0', '1', '4']];
        $map3['userid|userid1'] = $user['id'];
        $map3['status'] = '2';
        $buyorder = $Tgmx->alias('a')->join('fa_user b ','b.id= a.userid')->where($map)->order('tgid DESC')->limit(100)->select();
//        $saleorder = $Xymx->alias('a')->join('fa_user b ','b.id= a.userid')->where($map)->order('xyid DESC')->limit(20)->select();
        $tradeorder = $Ppmx->where($map2)->order('ppid DESC')->limit(100)->select();
        $completeorder = $Ppmx->where($map3)->order('ppid DESC')->limit(100)->select();
        $buylist = $Tgmx->alias('a')->join('fa_user b ','b.id= a.userid')->where('a.status', '0')->order('price DESC')->limit(10)->select();
//        $salelist = $Xymx->alias('a')->join('fa_user b ','b.id= a.userid')->where('a.status', '0')->order('price ASC')->limit(10)->select();
        $buytotal = $Tgmx->sum('number');
        if ($buylist) {
            foreach ($buylist as $key => $item) {
                $buylist[$key]['addtime'] = date('Y-m-d H:i', $item['addtime']);
                $buylist[$key]['num'] = $item['number'] - $item['buy_number'];
                $buylist[$key]['total'] = round($buylist[$key]['num'] * $item['price'], 2);
                $buylist[$key]['buy_total'] = round($item['buy_number'] * $item['price'], 2);
            }
        }
//        if ($salelist) {
//            foreach ($salelist as $key2 => $item2) {
//                $salelist[$key2]['addtime'] = date('Y-m-d H:i', $item2['addtime']);
//                $salelist[$key2]['num'] = $item2['number'] - $item2['sale_number'];
//                $salelist[$key2]['total'] = round($salelist[$key2]['num'] * $item2['price'], 2);
//            }
//        }

        if ($buyorder) {
            foreach ($buyorder as $key3 => $item3) {
                $buyorder[$key3]['pai'] = (int)((time() - $item3['addtime']) / 86400);
                $buyorder[$key3]['addtime'] = date('Y-m-d H:i', $item3['addtime']);
                $buyorder[$key3]['realname'] = $user['realname'];
            }
        }
        if ($completeorder) {
            foreach ($completeorder as $key6 => $item6) {
                $completeorder[$key6]['status']='已完成';
                $completeorder[$key6]['endtime']='已完成';
                $completeorder[$key6]['addtime']= date('m-d H:i', $item6['addtime']);
            }
        }
//        if ($saleorder) {
//            foreach ($saleorder as $key4 => $item4) {
//                $saleorder[$key4]['pai'] = (int)((time() - $item4['addtime']) / 86400);
//                $saleorder[$key4]['addtime'] = date('Y-m-d H:i', $item4['addtime']);
//                $saleorder[$key4]['realname'] = $user['realname'];
//            }
//        }
        if ($tradeorder) {
            foreach ($tradeorder as $key5 => $item5) {
                $end1 = $item5['addtime'] + $myset['zdqx'] * 3600;
                $end2 = $item5['paytime'] + $myset['zdqr'] * 3600;
                //卖家
                $tmp = [0 => '<a class="button" href="' . url('/index/business/dkdetail') . '?ppid=' . $item5['ppid'] . '" >买家待付款</a>',
                        1 => '<a class="button" href="' . url('/index/business/dkdetail') . '?ppid=' . $item5['ppid'] . '" >买家已付款待确认</a>',
                        2 => '已完成',
                        3 => '已取消',
                        4 => '<a class="button" " href="' . url('/index/business/dkdetail') . '?ppid=' . $item5['ppid'] . '" >投诉中</a>',];
                //买入
                $tmp2 = [0 => '<a class="button" href="/index/business/skdetail?ppid=' . $item5['ppid'] . '" >点击付款</a>',
                         1 => '<a class="button" href="' . url('/index/business/skdetail') . '?ppid=' . $item5['ppid'] . '" >已付款待对方确认</a>',
                         2 => '已完成',
                         3 => '已取消',
                         4 => '<a class="button" href="' . url('/index/business/skdetail') . '?ppid=' . $item5['ppid'] . '" >投诉中</a>',];
                //买入
                $tmp3 = [0 => '<span >剩余打款时间:</span><span style="font-size: smaller;" class="untime" data-time="' . $end1 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         1 => '<span >剩余确认时间:</span><span style="font-size: smaller;" class="toutime" data-time="' . $end2 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         2 => '已完成',
                         3 => '已取消',
                         4 => '投诉中'];
                //卖出
                $tmp4 = [0 => '<span >剩余打款时间:</span><span style="font-size: smaller;" class="toutime" data-time="' . $end1 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         1 => '<span >剩余确认时间:</span><span style="font-size: smaller;" class="untime" data-time="' . $end2 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         2 => '已完成',
                         3 => '已取消',
                         4 => '投诉中'];
                if ($item5['userid'] == $user['id']) {
                    $buytrade[] = [
                        'status'  => $tmp2[$item5['status']],
                        'endtime' => $tmp3[$item5['status']],
                        'addtime' => date('m-d H:i', $item5['addtime']),     //匹配时间
                        //                        'tgtime'=> date('m-d H:i', $item5['tgtime']),        //排单时间
                        'number'  => $item5['number'],
                        'username'  => $item5['account1'],
                        'ppsn'  => $item5['ppsn'],
                        'price'  => $item5['price'],
                        'total'  => $item5['total'],
                    ];
                } else {
                    $saletrade[] = [
                        'status'  => $tmp[$item5['status']],
                        'endtime' => $tmp4[$item5['status']],
                        'addtime' => date('m-d H:i', $item5['addtime']),      //匹配时间
                        //                        'xytime'=> date('m-d H:i', $item5['xytime']),         //排单时间
                        'number'  => $item5['number'],
                        'username'  => $item5['account'],
                        'ppsn'  => $item5['ppsn'],
                        'price'  => $item5['price'],
                        'total'  => $item5['total'],
                    ];
                }
            }
        }
        $this->assign('buyorder', $buyorder);  //个人买
//        $this->assign('saleorder', $saleorder);  //个人卖
        $this->assign('buytrade', $buytrade);   //个人买 交易
        $this->assign('saletrade', $saletrade); //个人卖 交易
        $this->assign('buylist', $buylist);  //买家列表
//        $this->assign('salelist', $salelist);  //卖家列表
        $this->assign('completeorder', $completeorder);  //已完成
        $this->assign('buytotal', $buytotal);  //求购总量
        $this->assign('sidebar', 2);  //交易亮灯


        return $this->fetch();
    }

    public function _empty()
    {
        return $this->fetch();
    }

    /**
     * 已完成 已取消
     */
    public function complete()
    {
        $user = $this->auth->getUser();
        $Ppmx =Db::name('ppmx');
        $map['userid|userid1'] = $user['id'];
        $map['status'] = 2;
        $tradeorder = $Ppmx->where($map)->order('ppid DESC')->paginate(10)->each(function($item, $key){
            $myset = config('site');
            $end1 = $item['addtime'] + $myset['zdqx'] * 3600;
            $end2 = $item['paytime'] + $myset['zdqr'] * 3600;
            //卖家
            $tmp = [0 => '<a style="color: red;" href="' . url('index/business/dkdetail') .'?ppid='.$item['ppid']. '" >买家待付款</a>',
                    1 => '<a style="color: green;" href="' . url('index/business/dkdetail') .'?ppid='.$item['ppid']. '" >买家已付款待确认</a>',
                    2 => '已完成',
                    3 => '已取消',
                    4 => '投诉'];
            //买入
            $tmp2 = [0 => '<a style="color: red;" href="' . url('index/business/skdetail') .'?ppid='.$item['ppid']. '" >点击付款</a>',
                     1 => '<a style="color: green;" href="' . url('index/business/skdetail') .'?ppid='.$item['ppid']. '" >已付款待对方确认</a>',
                     2 => '已完成',
                     3 => '已取消',
                     4 => '投诉'];
            //买入
            $tmp3 = [0 => '<div class="item-text" style="margin-left: 15px;">剩余打款时间:<span class="countTime" data-time="' . $end1 . '"></span></div>',
                     1 => '<div class="item-text" style="margin-left: 15px;">剩余确认时间:<span class="countTime" data-time="' . $end2 . '"></span></div>',
                     2 => '',
                     3 => '',
                     4 => ''];
            //卖出
            $tmp4 = [0 => '',
                     1 => '<div class="item-text" style="margin-left: 15px;">剩余确认时间:<span class="countTime" data-time="' . $end2 . '"></span></div>',
                     2 => '',
                     3 => '',
                     4 => ''];

            $k = (int)$item['status'];
            if ($item['userid'] == session('uid')) {
                $item['type'] = '买入币：' . $item['number'] . '个';
                $item['status'] = $tmp2[$k];
                $item['endtime'] = $tmp3[$k];
                $item['aa'] = url('index/business/skdetail' ) .'?ppid='.$item['ppid'];
            } else {
                $item['type'] = '卖出币：' . $item['number'] . '个';
                $item['status'] = $tmp[$k];
                $item['endtime'] = $tmp4[$k];
                $item['aa'] =  url('Business/dkdetail' ) .'?ppid='.$item['ppid'];
            }
            $item['addtime'] = date('m-d H:i', $item['addtime']);
            return $item;
        });
        $this->assign('page', $tradeorder->render());
        $this->assign('list', $tradeorder);   //个人交易
        return $this->fetch();
    }
    //排单
    public function gobuy()
    {
        $price = Db::name('tzrank')->order('rprice asc')->select();
        $this->assign('price', $price);
        return $this->fetch();
    }

    //虚拟币认购
    public function hyrg()
    {
        $myset = config('site');
        $open=0;
        if(strtotime($myset['rgstart'])  < time() && strtotime($myset['rgend'])  > time()){
            $open = 1;
        }
        if($myset['rgswith']==0){
            $open=0;
        }else{
            $open = 1;
        }

        $user = $this->auth->getUserinfo();
        if($user['id']==4861||$user['id']==36){
            $open = 1;
        }

        $opentime = date('m/d/Y H:i:s',strtotime($myset['rgstart'])-28800);
        $list = Db::name('recharge')->whereTime('ctime', 'today')->limit(5)->select();
        $this->assign('open', $open);
        $this->assign('opentime', $opentime);
        $this->assign('list', $list);
        return $this->fetch();
    }

    //认购接入支付
    public function pay(){

        $data =  $this->request->param();

        $userid='1306249865';	//接入商户ID
        $userkey='4d3a9bf4cc8fca44685a09720f8395bde80906c8';	//接入密钥

        $price = $data['price'];
        $version='2.1';
        $customerid=$userid;
        $sdorderno=$data['sdorderno'];
        $total_fee=number_format($price,2,'.','');//订单金额
        $notifyurl='http://'.$_SERVER['HTTP_HOST'].'/index/trade/notify.html';
        $returnurl='http://'.$_SERVER['HTTP_HOST'].'/index/trade/payreturn.html';

        $map['version'] = $version;
        $map['customerid'] = $userid;
        $map['sdorderno'] = $sdorderno;
        $map['total_fee'] = $total_fee;
        $map['paytype'] = $data['paytype'];//支付方式
        $map['notifyurl'] = $notifyurl;
        $map['returnurl'] = $returnurl;
        $map['sign'] = md5('version='.$version.'&mch_id='.$customerid.'&total_fee='.$total_fee.'&out_trade_no='.$sdorderno.'&notify_url='.$notifyurl.'&return_url='.$returnurl.'&'.$userkey);

        log::info($map);
        $this->assign('map',$map);
        return $this->fetch();
    }
    public function notify(){
        $userid='1306249865';	//接入商户ID
        $userkey='4d3a9bf4cc8fca44685a09720f8395bde80906c8';	//接入密钥


        $status=$_POST['status'];
        $customerid=$_POST['mch_id'];
        $sdorderno=$_POST['out_trade_no'];
        $total_fee=$_POST['total_fee'];
        $paytype=$_POST['pay_type'];
        $sdpayno=$_POST['sdpayno'];
        $sign=$_POST['sign'];

        $mysign=md5('mch_id='.$customerid.'&status='.$status.'&sdpayno='.$sdpayno.'&out_trade_no='.$sdorderno.'&total_fee='.$total_fee.'&pay_type='.$paytype.'&'.$userkey);
        log::info('1----------------------------');
        log::info(input('post.'));
        if($sign==$mysign){
            if($status=='1'){
                $info = db('recharge')->where(array('order_sn'=>$sdorderno))->find();
                if($info['pay_status']!='1'){
                    caiwu($info['user_id'], $info['pay_num'], 11, 'v1', '购买安全码');
                    db('recharge')->where(array('order_sn'=>$sdorderno))->setField('pay_status', '1');
                    $payback='付款成功';
                }else{
                    $payback='已经付款，请勿重复提交';
                }
            } else {
                $payback='付款失败';
            }
        } else {
            $payback='签名错误';
        }
        $this->redirect('index/index/successinfo',array('payback'=>$payback));
    }
    public function payreturn(){
        $userid='1306249865';	//接入商户ID
        $userkey='4d3a9bf4cc8fca44685a09720f8395bde80906c8';	//接入密钥

        $status=$_GET['status'];
        $customerid=$_GET['mch_id'];
        $sdorderno=$_GET['out_trade_no'];
        $total_fee=$_GET['total_fee'];
        $paytype=$_GET['pay_type'];
        $sdpayno=$_GET['sdpayno'];
        $sign=$_GET['sign'];

        $mysign=md5('mch_id='.$customerid.'&status='.$status.'&sdpayno='.$sdpayno.'&out_trade_no='.$sdorderno.'&total_fee='.$total_fee.'&pay_type='.$paytype.'&'.$userkey);
        log::info(input());
        $payback='付款成功';
        if($sign==$mysign){
            if($status=='1'){
                $payback='付款成功';
            } else {
                $payback='付款失败';
            }
        } else {
            $payback='签名错误';
        }
        $this->redirect('index/index/successinfo',array('payback'=>$payback));
    }

    public function buyajax()
    {
        $price = input('price',0);
        $Xymx = Db::name('Xymx');
        $salelist = $Xymx->alias('a')->join('fa_user b ','b.id= a.userid')->where(['a.status'=>'0','account'=>$price])->order('price ASC')->limit(10)->select();

        if ($salelist) {
            foreach ($salelist as $key2 => $item2) {
                $salelist[$key2]['addtime'] = date('Y-m-d H:i', $item2['addtime']);
                $salelist[$key2]['num'] = $item2['number'] - $item2['sale_number'];
                $salelist[$key2]['total'] = round($salelist[$key2]['num'] * $item2['price'], 2);
            }
        }

        $this->assign('salelist', $salelist);  //卖家列表
        return $this->fetch();
    }

    public function saleajax()
    {
        $price = input('price',0);
        $Tgmx = Db::name('Tgmx');
        $buylist = $Tgmx->alias('a')->join('fa_user b ','b.id= a.userid')->where(['a.status'=>'0','account'=>$price])->order('price DESC')->limit(10)->select();
        if ($buylist) {
            foreach ($buylist as $key => $item) {
                $buylist[$key]['addtime'] = date('Y-m-d H:i', $item['addtime']);
                $buylist[$key]['num'] = $item['number'] - $item['buy_number'];
                $buylist[$key]['total'] = round($buylist[$key]['num'] * $item['price'], 2);
                $buylist[$key]['buy_total'] = round($item['buy_number'] * $item['price'], 2);
            }
        }

        $this->assign('buylist', $buylist);  //买家列表

        return $this->fetch();
    }

    /**
     * 获取更多
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getmore()
    {

        $Tgmx = Db::name('Tgmx');
        $buylist = $Tgmx->alias('a')
            ->join('fa_user b ','b.id= a.userid')
            ->where(['a.status'=>'0'])
            ->order('tgid DESC')
            ->paginate(10)
            ->each(function($item, $key){
            $item['addtime']=date('Y-m-d H:i', $item['addtime']);
            $item['num']=$item['number'] - $item['buy_number'];
            return $item;
        });;


        $this->assign('buylist', $buylist);  //买家列表

        return $this->fetch('saleajax');
    }
}
