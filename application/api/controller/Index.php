<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\Session;

/**
 * 首页接口
 */
class Index extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     * 
     */
    public function index()
    {
        $this->success('请求成功');
    }


    private function xRandom($red_min, $red_max){
        $sqr = ($red_max-$red_min) * ($red_max-$red_min);
        $rand_num = mt_rand(0, ($sqr-1));
//        return sqrt($rand_num);
        return number_format($red_min + mt_rand()/mt_getrandmax() * ($red_max-$red_min), 1);
    }

    /**
     * 是否抢过第一次红包
     */
    public function getIsGrabbed()
    {
        $userInfo = $this->auth->getUserinfo();

        $FreeRedPacketTimes = DB::name('redpacket')->where(['type'=>1, 'user_id'=>$userInfo['id']])->count();
        if ($FreeRedPacketTimes >= 1){
            $this->error('已抢过一次', ['isGrabbed'=>1]);
        }else{
            $this->success('还没抢过一次', ['isGrabbed'=>0]);
        }
    }

    //首页随机红包
    public function getRedpackage()
    {
            //会员id
            $userInfo = $this->auth->getUserinfo();
            if ($userInfo['issm'] == 0){
                $this->error("未实名认证,请先实名认证");
            }



            $userId = $userInfo['id'];
            //会员等级
            $userLevel = $userInfo['level'];

            $red_min = config('site.redmin');
            $red_max = config('site.redmax');

            //获得随机红包
            $red_amount = $this->xRandom($red_min, $red_max);

//            $count = DB::name('caiwu')->where(['type'=>15, 'userid'=>$userId])->whereTime('addtime', 'today')->count();

            $FreeRedPacketTimes = DB::name('redpacket')->where(['type'=>1, 'user_id'=>$userId])->count();
            $ChargeRedPacketTimes = DB::name('redpacket')->where(['type'=>2, 'user_id'=>$userId])->whereTime('created_at', 'today')->count();

            if ($FreeRedPacketTimes  < 1 && $ChargeRedPacketTimes < 3){
                caiwu($userId, $red_amount, 15, 'wall2', "免费抢红包一次");
                DB::name('redpacket')->insert(['user_id'=>$userId, 'type'=>1, 'amount'=>$red_amount, 'price'=>0, 'created_at'=>time(), 'updated_at'=>time()]);
                $this->success('恭喜您, 抢得'.$red_amount.'LMC红包');
            } else if ($FreeRedPacketTimes  >= 1 && $ChargeRedPacketTimes < 3){
                if ($userInfo['wall2']<3){
                    $this->error('账户余额不足', $userInfo);
                }
                caiwu($userId, $red_amount, 15, 'wall2', "收费抢红包一次");
                caiwu($userId, -3, 18, 'wall2', "抢红包所花费用");
                DB::name('redpacket')->insert(['user_id'=>$userId, 'type'=>2, 'amount'=>$red_amount, 'price'=>3, 'created_at'=>time(), 'updated_at'=>time()]);
                $this->success('恭喜您, 抢得'.$red_amount.'LMC红包');
            } else if ($FreeRedPacketTimes  >= 1 &&$ChargeRedPacketTimes >= 3) {
                    $this->error("
                                           活动规则：
                                            1.注册成为志愿者，免费获得领取红包一次。
                                            2.正式会员每次可使用3枚LMC参与抢红包活动，每日仅限3次。");
            }
    }

}
