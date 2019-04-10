<?php

namespace app\admin\controller;

use addons\epay\library\Service;
use app\common\controller\Backend;
use think\Db;

/**
 * 测试管理.
 *
 * @icon fa fa-circle-o
 */
class Test extends Backend
{
    public function clear()
    {
        // 清除表中全部记录，重置自增主键为 1
//        Db::query("TRUNCATE TABLE fa_ppmx");
//        Db::query("TRUNCATE TABLE fa_xymx");
//        Db::query("TRUNCATE TABLE fa_tgmx");
//        Db::query("TRUNCATE TABLE fa_ppmx2");
//        Db::query("TRUNCATE TABLE fa_xymx2");
//        Db::query("TRUNCATE TABLE fa_tgmx2");
//        Db::query("TRUNCATE TABLE fa_dong");
//        Db::query("TRUNCATE TABLE fa_caiwu");
//        Db::query("TRUNCATE TABLE fa_user");

//        Db::name('user')->where('id','>',0)->setField([
//                'wall1'=>1000,
//                'wall2'=>1000,
//                'wall3'=>0,
//                'wall4'=>0,
//                'wall5'=>0,
//                'wall6'=>2000,
//                'wall7'=>100,
//                'wall8'=>100,
//                'futou'=>0,
//                'txwall6'=>0,
//                'txwall3'=>0,
//                'jointime'=>time(),
//                'slrate'=>0,
//                ]);

//        update_user_tui2(41);
//        jcj(10,1000);
        //db('caiwu')->where('type',6)->setField('type',8);
//       $re= db('caiwu')->where('type',9)->where(['like','%奖%'])->select();
//        dump($re);

//        $contents = json_decode(file_get_contents(url('api/kline/index')),true);
//        $bb = json_decode(file_get_contents(url('/api/kline/index')),true);
//       $re= controller('api/kline')->index();
//        $aa = json_decode($re);
//        var_dump($bb);
//         Db::name('cart')->where('id>0')->setField('spec_key',0);

        //火币网 虚拟币行情
//        $contents = json_decode(file_get_contents('https://www.huobi.co/-/x/general/index/constituent_symbol/detail'), true);
        ////        dump($contents['data']['symbols']);
//        $lists = $contents['data']['symbols'];
//        $lmc = Db::name('kline')->order('id desc')->cache(true, 600)->find();
//        $lmcrate = $lmc['close'];
//        foreach ($lists as $list) {
//            $list['lmc']=round($list['close'] / $lmcrate,4);
//            $tmp[$list['symbol']] =$list;
//        }
        //火币网 虚拟币行情
//        $contents = json_decode(file_get_contents('https://www.huobi.co/-/x/general/index/constituent_symbol/detail'), true);
        ////        dump($contents['data']['symbols']);
//        $lists = $contents['data']['symbols'];
//        $k='';
//        foreach ($lists as $list) {
//            $tmp[$list['symbol']] =$list;
//            $k .=$list['symbol'].'/';
//        }
//        dump($k);dump($tmp);

//        $user=['id'=>4825];
//       $re= Db::name('user')->where("id>0")->setField('wall7',0);
//       dump($re);
//        $re2=  Db::name('caiwu')->where('ptype','wall7')->delete();
//        dump($re2);
//        update_user_tui(3358);
//        $users= Db::name('user')->select();
//        foreach ($users as $user){
//            $tmp[]=$user['id'];
//        }
//
//        Db::name('chart_groupuser')->insert(['uids'=>json_encode($tmp)]);
        //创建支付对象
//         $pay = Service::createPay('wechat');

        // //构建订单信息
//         $order = [
//             'out_trade_no' => time(),
//             'total_fee' => '1', // **单位：分**
//             'body' => 'test body - 测试',

//         ];

        // //跳转或输出
//         return $pay->mp($order)->send();
        // $user = db('user')->find(8027);
        // $addr = explode(' ', $user['address']);
    }
}
