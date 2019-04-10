<?php

namespace addons\shop\controller;
 
use think\addons\Controller; 
use think\Db; 

class Index extends Controller
{ 
    protected $noNeedLogin = 'test';
    protected $noNeedRight = '*';
    protected $layout = '';
     public function _initialize()
    {
        parent::_initialize();
        \config('default_ajax_return','html');
    }

    public function _empty()
    {
        return $this->fetch();
    }

     public function index(){
          return $this->fetch();
     }
 
    public function shop(){
        $hot_goods =Db::name('shop_goods')->where( ['is_hot'=>1,'is_on_sale'=>1])->order('goods_id DESC')->limit(20)->cache(true)->select();//首页热卖商品

        $this->assign('hot_goods',$hot_goods);
        $favourite_goods = Db::name('shop_goods')->where(['is_recommend'=>1,'is_on_sale'=>1])->order('goods_id DESC')->limit(20)->cache(true)->select();//首页推荐商品
        $this->assign('favourite_goods',$favourite_goods);
        $newgoods = Db::name('shop_goods')->where(['is_new'=>1,'is_on_sale'=>1])->order('goods_id DESC')->limit(20)->cache(true)->select();//首页新品商品
        $this->assign('newgoods',$newgoods);

        return $this->fetch();
    }

//    /**
//     * @return View
//     */
//    public function test()
//    {
//
//    return $this->fetch();
////        $face = face();
////        echo $face;
//    }
    public function upload(){
        $file = request()->file('image');
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                $image = $info->getSaveName();
                $image = 'http://yzg222.lfvip66.com/uploads/' . $image;
                face($image);
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
        echo $image;
    }
 

    //话费充值页面
    public function recharge_hf()
    {
        //充值记录
        $user = $this->auth->getUserinfo();
        $recharge_hf_list = DB::name('caiwu')->where(['userid'=>$user['id'], 'type'=>19])->select();
        $hf100 =  need_lmc(30);
        $hf50 =  need_lmc(50);
        $this->assign('recharge_list', $recharge_hf_list);
        $this->assign('hf100', $hf100);
        $this->assign('hf50', $hf50);
        return $this->fetch();
    }

    //流量充值页面
    public function recharge_ll()
    {
        $user = $this->auth->getUserinfo();
        $recharge_ll_list = DB::name('caiwu')->where(['userid'=>$user['id'], 'type'=>20])->select();
        $ll100 =  need_lmc(100);
        $this->assign('ll100', $ll100);
        $this->assign('recharge_list', $recharge_ll_list);
        return $this->fetch();
    }

    //油卡充值页面
    public function recharge_yk()
    {
        $user = $this->auth->getUserinfo();
        $recharge_ll_list = DB::name('caiwu')->where(['userid'=>$user['id'], 'type'=>21])->select();
        $yk100 =  need_lmc(100);
        $this->assign('yk100', $yk100);
        $this->assign('recharge_list', $recharge_ll_list);
        return $this->fetch();
    }
    //燃气充值页面
    public function recharge_rq()
    {
        $user = $this->auth->getUserinfo();
        $recharge_list = DB::name('caiwu')->where(['userid'=>$user['id'], 'type'=>24])->select();
        $this->assign('recharge_list', $recharge_list);
        return $this->fetch();
    }

    //电费充值页面
    public function recharge_df()
    {
        $user = $this->auth->getUserinfo();
        $recharge_list = DB::name('caiwu')->where(['userid'=>$user['id'], 'type'=>23])->select();
        $this->assign('recharge_list', $recharge_list);
        return $this->fetch();
    }
    //水费充值页面
    public function recharge_sf()
    {
        $user = $this->auth->getUserinfo();
        $recharge_list = DB::name('caiwu')->where(['userid'=>$user['id'], 'type'=>22])->select();
        $this->assign('recharge_list', $recharge_list);
        return $this->fetch();
    }


    //火车票预订
    public function  traintickets()
    {
        $user = $this->auth->getUserinfo();
        $tickets_order_list = TrainOrder::all(['user_id'=>$user['id']]);
        $this->assign('tickets_order_list', $tickets_order_list);
        return $this->fetch();
    }

    //火车票查询列表
    public function ticketlist()
    {
        $list = [];
        $reason = '';
        $from_station = $this->request->param('from_station');
        $to_station = $this->request->param('to_station');
        $from_city_code = $this->request->param('from_city_code');
        $to_city_code = $this->request->param('to_city_code');
        $train_date = $this->request->param('train_date');

        $available  = $this->getAvailable($from_city_code, $to_city_code, $train_date);
        if ($available['error_code'] == 0){
            $list = $available['result']['list'];
            foreach ($list as $key=>&$value){
                $value['ticket_price'] = 0;
                if (in_array($value['train_type'],['G', 'D'])){
                    if ($value['edz_num'] != '--' && $value['edz_num'] > 0){
                        $value['ticket_price'] = $value['edz_price'];
                    }else{
                        unset($list[$key]);
                    }
                } else {
                    if ($value['yz_price']) {
                        $value['ticket_price'] = $value['yz_price'];
                    } else {
                        unset($list[$key]);
                    }
                }
                $run_time = explode(':', $value['run_time']);
                $value['run_time'] = $run_time[0].'时'.$run_time[1].'分';
            }
        }else{
            $reason = $available['reason'];
        }
//        print_r($list);
        
        $this->assign('from_station', $from_station);
        $this->assign('to_station', $to_station);
        $this->assign('list', $list);
        $this->assign('reason', $reason);
        $this->assign('train_date', $train_date);

        return $this->fetch();
    }

    public function ticketlist_card()
    {
        $start_time = $this->request->param('start_time');
        $arrive_time = $this->request->param('arrive_time');
        $train_code = $this->request->param('train_code');
        $from = $this->request->param('from');
        $from_code = $this->request->param('from_code');
        $to = $this->request->param('to');
        $to_code = $this->request->param('to_code');
        $run_time = $this->request->param('run_time');
        $ticket_price = $this->request->param('ticket_price');
        $train_type = $this->request->param('train_type');
        $train_date = $this->request->param('train_date');

        $start_station_name = $this->request->param('start_station_name');
        $end_station_name = $this->request->param('end_station_name');


        $this->assign('start_time', $start_time);
        $this->assign('arrive_time', $arrive_time);
        $this->assign('train_code', $train_code);
        $this->assign('train_date', $train_date);
        $this->assign('from', $from);
        $this->assign('to', $to);
        $this->assign('run_time', $run_time);
        $this->assign('ticket_price', $ticket_price);
        $this->assign('train_type', $train_type);
        $this->assign('from_code', $from_code);
        $this->assign('to_code', $to_code);

        $this->assign('start_station_name', $start_station_name);
        $this->assign('end_station_name', $end_station_name);

        return $this->fetch();
    }



    //违章代缴
    public function violation_rule()
    {

        return $this->fetch();
    }


}