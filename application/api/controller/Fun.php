<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\Log;

/**
 * 发奖接口
 */
class Fun extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];

    //虚拟币每小时检查交易情况
    function dong()
    {
        $myset = config('site');
        $zdqx = $myset['zdqx'];    //小时 未打款，取消匹配，封号未打款人，卖单回大厅
        $time12 = time() - $zdqx * 3600;
        log::info('-------------' . $time12);
        $ppmx = db('ppmx');
        $buys = $ppmx->where(['addtime' => ['lt', $time12], 'status' => 0])->select();
        if ($buys) {
            foreach ($buys as $buy) {
                ppcancle($buy['ppid']);   //取消匹配
                dongjie($buy['userid'], $buy['account'],'封号未打款人'); //封号未打款人
            }
        }

//        $zdqr = $myset['zdqr'];    //小时 未确认 ，封号卖家
//        $time13 = time() - $zdqr * 3600;
//        $sales = $ppmx->where(['paytime' => ['lt', $time13], 'status' => 1])->select();
//        if ($sales) {
//            foreach ($sales as $sale) {
//                dongjie($sale['userid1'], $sale['account1'],'规定时间内未确认，封号卖家');  //封号卖家
//            }
//        }

    }

    //互助每小时检查交易情况
    function dong2()
    {
        $myset = config('site');
        $zdqx = $myset['zdqx'];    //小时 未打款，取消匹配，封号未打款人，卖单回大厅
        $time12 = time() - $zdqx * 3600;
        \Think\Log::record('-------------' . $time12);
        $ppmx = db('ppmx2');
        $buys = $ppmx->where(['addtime' => ['lt', $time12], 'status' => 0])->select();
        if ($buys) {
            foreach ($buys as $buy) {

                \Think\Log::info($buy);
                ppcancle2($buy['ppid']);   //取消匹配
                dongjie2($buy['userid'],$buy['account'], '封号未打款人'); //封号未打款人
            }
        }

        $zdqr = $myset['zdqr'];    //小时 未确认 ，封号卖家
        $time13 = time() - $zdqr * 3600;
        $sales = $ppmx->where(['paytime' => ['lt', $time13], 'status' => 1])->select();
        if ($sales) {
            foreach ($sales as $sale) {
//            $ppmx->where(['ppid'=>$sale['ppid']])->setField('state',3);   //取消匹配
                dongjie2($sale['userid1'],$sale['account1'], '规定时间内未确认，封号卖家');  //封号卖家
//            $xymx= M('xymx');
//            $xymx->where(['xyid'=>$sale['xyid']])->setDec('saled_number',$sale['price']);
//            $xymx->where(['xyid'=>$sale['xyid']])->setField('state',0);  //卖单回大厅
            }
        }
    }



}
