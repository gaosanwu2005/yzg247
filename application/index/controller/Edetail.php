<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;

class Edetail extends Frontend
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
     * 我的财务
     */
    public function bonus()
    {
        $ptype = input('type','');
        $cwtype = input('cwtype','');

        $rshy = $this->auth->getUser();
        $map['userid']=$rshy['id'];
        $map['ptype']=$ptype;
        $map['type']=$cwtype;
        $map=array_filter($map);

        $list = Db::name('caiwu')
            ->where($map)
            ->order('id desc')
            ->paginate(20,false,['query' => $map])
            ->each(function($item, $key){
                $site =  config('site');
                $walltype =$site['walltype'];
                $caiwuarray=$site['caiwuarray'];
                $item['ptype']=$walltype[ $item['ptype']];
                $item['type']=$caiwuarray[ $item['type']];
                return $item;
            });
        
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * BTC 充币记录
     */
    public function btc_charge()
    {
        $ptype = input('type','');

        $rshy = $this->auth->getUser();
        $map['user_id']=$rshy['id'];
        $map['type']=$ptype;
        $map=array_filter($map);

        $list = Db::name('tx_eth2lmc')
            ->where($map)
            ->order('id desc')
            ->paginate(20,false,['query' => $map])
            ->each(function($item, $key){
                $st=['待充值','成功','失败'];
                $item['ptype']=$item['paytype'];
                $item['price']=$item['amount'];
                $item['type']='数字资产';
                $item['memo']=$item['paytype'].'充币'.$st[$item['status']];

                return $item;
            });

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch('bonus');
    }

    /**
     * BTC  提现记录
     */
    public function btc_tixian()
    {
        $ptype = input('type','');

        $rshy = $this->auth->getUser();
        $map['user_id']=$rshy['id'];
        $map['type']=$ptype;
        $map=array_filter($map);

        $list = Db::name('tx_lmc2eth')
            ->where($map)
            ->order('id desc')
            ->paginate(20,false,['query' => $map])
            ->each(function($item, $key){
                $st=['待充值','成功','失败'];
                $item['ptype']=$item['paytype'];
                $item['price']=$item['amount'];
                $item['type']='数字资产';
                $item['memo']=$item['paytype'].'提币'.$st[$item['status']];

                return $item;
            });

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch('bonus');
    }


}
