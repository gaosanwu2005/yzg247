<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use Think\Db;
use Think\Log;

class Trade2 extends Frontend
{
    protected $noNeedRight = ['*'];
    public function index()
    {
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
        $Ppmx =Db::name('ppmx2');
        $map['userid|userid1'] = $user['id'];
//        $map['status'] = 2;
        $tradeorder = $Ppmx->where($map)->order('ppid DESC')->paginate(5)->each(function($item, $key){
            $myset = config('site');
            $end1 = $item['addtime'] + $myset['zdqx'] * 3600;
            $end2 = $item['paytime'] + $myset['zdqr'] * 3600;
            //卖家
            $tmp = [0 => '<a style="color: red;" href="' . url('index/business2/dkdetail') .'?ppid='.$item['ppid']. '" >买家待付款</a>',
                    1 => '<a style="color: green;" href="' . url('index/business2/dkdetail') .'?ppid='.$item['ppid']. '" >买家已付款待确认</a>',
                    2 => '已完成',
                    3 => '已取消',
                    4 => '投诉'];
            //买入
            $tmp2 = [0 => '<a style="color: red;" href="' . url('index/business2/skdetail') .'?ppid='.$item['ppid']. '" >点击付款</a>',
                     1 => '<a style="color: green;" href="' . url('index/business2/skdetail') .'?ppid='.$item['ppid']. '" >已付款待对方确认</a>',
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
                $item['aa'] = url('index/business2/skdetail' ) .'?ppid='.$item['ppid'];
            } else {
                $item['type'] = '卖出币：' . $item['number'] . '个';
                $item['status'] = $tmp[$k];
                $item['endtime'] = $tmp4[$k];
                $item['aa'] =  url('business2/dkdetail' ) .'?ppid='.$item['ppid'];
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

    public function chosebuyajax()
    {
        $price = input('price',1000);
        $Xymx2 = Db::name('Xymx2');
        $salelist = $Xymx2->alias('a')->join('fa_user b ','b.id= a.userid')->where(['a.status'=>'0','number'=>$price])->order('price ASC')->limit(10)->select();

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

    public function chosesaleajax()
    {
        $price = input('price',1000);
        $Tgmx2 = Db::name('Tgmx2');
        $buylist = $Tgmx2->alias('a')->join('fa_user b ','b.id= a.userid')->where(['a.status'=>'0','number'=>$price])->order('price DESC')->limit(10)->select();
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
}
