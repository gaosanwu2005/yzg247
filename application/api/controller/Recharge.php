<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

class Recharge extends Api
{
    protected $noNeedLogin = ['login'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    protected function generate($type)
    {
        //防止重复提交
        $c = md5(serialize($this->request->request()));
        $find = session($c);
        if ($find) {
            if ($find['expire'] + 2 - time() >= 0) {
                return false;
            }
        }
        session($c, array('expire' => time()));
        $data=input();
        $user = $this->auth->getUserinfo();
        $myset = config('site');
        if (time() < strtotime($myset['chargeopen']) || time() >= strtotime($myset['chargeclose'])) {
            $this->error("每日账户仅限充值一次。充值时间：工作日{$myset['chargeopen']}--{$myset['chargeclose']}");
        }
        $mobile = $this->request->request('mobile');
        $prevalue = $this->request->request('prevalue');
        $username = $this->request->request('username',$user['username']);
        $userId = $user['id'];
        switch ($type){
            case 'mobile':
                $typeStr = '手机充值';
                $CaiWuType = 19;
                break;
            case 'flow':
                $typeStr = '流量充值';
                $CaiWuType = 20;
                break;
            case 'oil':
                $typeStr = '油卡充值';
                $CaiWuType = 21;
                break;
            case 'water':
                $typeStr = '水费缴纳';
                $CaiWuType = 22;
                if(empty($data['city'])||empty($data['institutions'])||empty($data['usersn'])||empty($data['username'])){
                    $this->error('信息不完善，请完善后提交');
                }
                break;
            case 'electricity':
                $typeStr = '电费缴纳';
                $CaiWuType = 23;
                if(empty($data['city'])||empty($data['institutions'])||empty($data['usersn'])||empty($data['username'])){
                    $this->error('信息不完善，请完善后提交');
                }
                break;
            case 'gas':
                $typeStr = '燃气缴纳';
                $CaiWuType = 24;
                if(empty($data['city'])||empty($data['institutions'])||empty($data['usersn'])||empty($data['username'])){
                    $this->error('信息不完善，请完善后提交');
                }
                break;
            default:
                $typeStr = '手机充值';
                $CaiWuType = 19;
        }
        if (in_array($CaiWuType, [19,20]) && $user['slrate']<10){
            $this->error("算力不足10G");
        }
        if (in_array($CaiWuType, [21,22, 23, 24]) && $user['slrate']<100){
            $this->error("算力不足100G");
        }
        if($user['star']<$myset['shopxin']){
            $this->error('信用达到'.$myset['shopxin'].',才可以交易,你的信用是'.$user['star'] );
        }
        $count = Db::name('recharge_third')->where(['user_id' => $user['id']])->whereTime('ctime', 'today')->count();
        if($count > 0){
            $this->error('今日已申请过充值，请明日再试', 789);
        }
        $lmc = need_lmc($prevalue);
        $need = $lmc[0]*($myset['shopczfee']*0.01+1);
        if ($user['wall2'] < $need){
            $this->error('您的余额不足');
        }
        //   判断授信金额是否够
        if ($user['wall7'] < $need) {
            $this->error('授信额度不足');
        }
        if (!is_numeric($prevalue)) {
            $this->error('请选择面值');
        }
        if($type =='mobile'||$type =='flow'){
            if (!$mobile || !is_numeric($mobile)) {
                $this->error('请输入手机号'.$type);
            }
            if (!preg_match("/^1[3456789]\d{9}$/", $mobile)) {
                $this->error('请输入合法的手机号');
            }
        }

        //扣用户款

        $orderId = time() . rand(100, 999) . 'SN';
        $data['order_sn'] = $orderId;
        $data['mobile'] = $mobile;
        $data['user_id'] = $userId;
        $data['oil_account_number'] = $this->request->request('game_userid');
        $data['username'] = $username;
        $data['amount'] = $prevalue;
        $data['ctime'] = time();
        $data['payment'] = "wall2";
        $data['status'] = 0;
        $data['num'] = $need;
        $data['lmcprice'] = $lmc[1];
        $data['type'] = $typeStr;
        $re=Db::name('recharge_third')->strict(false)->insert($data);
        if ($re) {
            //扣用户款
            caiwu($userId, -$need, $CaiWuType, 'wall2', $typeStr,1);
            caiwu($userId, -$need, $CaiWuType, 'wall7', $typeStr,1);

            $this->success('提交成功',789);
        }else{
            $this->error('提交失败');
        }
    }

    //充话费
    public function mobile()
    {
        if ($this->request->isPost()) {
            $this->generate('mobile');
        }
        $this->error('非法操作');
    }

    //充流量
    public function flow()
    {
        if ($this->request->isPost()) {
            $this->generate('flow');
        }
        $this->error('非法操作');
    }

    //油卡充值
    public function oil()
    {
        if ($this->request->isPost()) {
            $this->generate('oil');
        }
        $this->error('非法操作');
    }

    //水费缴纳
    public function water()
    {
        if ($this->request->isPost()) {
            $this->generate('water');
        }
        $this->error('非法操作');
    }

    //电费缴纳
    public function electricity()
    {
        if ($this->request->isPost()) {
            $this->generate('electricity');
        }
        $this->error('非法操作');
    }
    //燃气缴纳
    public function gas()
    {
        if ($this->request->isPost()) {
            $this->generate('gas');
        }
        $this->error('非法操作');
    }


}