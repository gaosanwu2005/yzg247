<?php
namespace app\api\juhe;


trait TrainTickets {

    private $train_openid = 'JHf3cca0c303d1623e2d8e98e9471d4f17';
    private $train_appkey = 'c185b6563bb34f32a8331ef8babad1b3';

    private $train_cityCode = 'http://op.juhe.cn/trainTickets/cityCode';
    private $train_ticketsAvailable= 'http://op.juhe.cn/trainTickets/ticketsAvailable';
    private $train_submit = 'http://op.juhe.cn/trainTickets/submit';
    private $train_orderStatus = 'http://op.juhe.cn/trainTickets/orderStatus';
    private $train_pay = 'http://op.juhe.cn/trainTickets/pay';
    private $train_setPush = 'http://op.juhe.cn/trainTickets/setPush';

   //站点简码查询
   private function getCityCode($trainName)
   {
       $content = $this->juhecurl($this->train_cityCode, 'key='.$this->train_appkey.'&stationName='.$trainName);
       $result = $this->_returnArray($content);
       return $result;
   }

   //余票查询
    private function getAvailable($from_city_code, $to_city_code, $train_date)
    {
        $content = $this->juhecurl($this->train_ticketsAvailable, 'key='.$this->train_appkey.'&train_date='.$train_date.'&from_station='.$from_city_code.'&to_station='.$to_city_code);
        $result = $this->_returnArray($content);
        return $result;
    }

    //提交订单
    private function train_submit($user_orderid, $from_code,$from_name, $to_code, $to_name, $train_code, $train_date, $ticket_price, $idCard, $truename, $piaotype, $train_type)
    {
        $piaotypeStr = function () use ($piaotype){
            if ($piaotype == 1){
                $str = '成人票';
            } else if($piaotype == 2) {
                $str = '儿童票';
            } else if ($piaotype == 4) {
                $str = '残军票';
            } else {
                $str = '未知类型';
            }
            return $str;
        };
        $zwcode=array("A","B","C","D","F");
        $passengersArray = array(
            array(
                'passengerid' => 1, //乘客的顺序号，当有多个乘客时，每个人的乘客号要唯一
                'passengersename' => $truename, //请替换成真实的名字
                'piaotype' => $piaotype, //请仔细查看官网文档中piaotype和piaotypename的对应关系，不可出错
                'piaotypename' => $piaotypeStr(),
                'passporttypeseid' => '1', //请仔细查看官网文档中passporttypeseid和passporttypeseidname的对应关系，不可出错
                'passporttypeseidname' => '二代身份证',
                'passportseno' => $idCard, //请替换成真实的身份证号码
                'price' => $ticket_price, //填写真实的价格
                'zwcode' => in_array($train_type, ['D', 'G'])?"O":1, //请确定您选择的车次中是否真有此类座次
                'zwname' => in_array($train_type, ['D', 'G'])?"二等座":"硬座",
            )
        );
        $postArray = array(
            'key' => $this->train_appkey,
            'checi' => $train_code, //从上一步查询中发现G226有余票
            'from_station_code' => $from_code, //出发站的简码，注意不是SZH（苏州）
            'from_station_name' => $from_name, //出发站的名字，务必和出发站的简码对应
            'to_station_code' => $to_code, //到达车站的简码
            'to_station_name' => $to_name, //到达车站的名字，务必和到达车站的简码对应
            'train_date' => $train_date, //乘车日期，注意时间的格式
            'user_orderid' => $user_orderid, //乘车日期，注意时间的格式
            'passengers' => json_encode($passengersArray, JSON_UNESCAPED_UNICODE),
        );
        $postStr = '';
        foreach ($postArray as $key => $value) {
            $postStr .= '&'.$key.'='.$value;
        }

        $content = $this->juhecurl($this->train_submit, $postStr);
        $result = $this->_returnArray($content);
        return $result;
    }

    //请求出票
    private function train_pay($orderid)
    {
        $content = $this->juhecurl($this->train_pay, 'key='.$this->train_appkey.'&orderid='.$orderid);
        $result = $this->_returnArray($content);
        return $result;
    }

    //订单查询
    private function train_orderStatus($orderid)
    {
        $content = $this->juhecurl($this->train_orderStatus, 'key='.$this->train_appkey.'&orderid='.$orderid);
        $result = $this->_returnArray($content);
        return $result;
    }

    private function set_callback($submit_callback, $pay_callback, $refund_callback)
    {
        $content = $this->juhecurl($this->train_setPush, "key=".$this->train_appkey."&submit_callback=".$submit_callback.'&pay_callback='.$pay_callback.'&refund_callback='.$refund_callback);
        $result = $this->_returnArray($content);
        return $result;
    }

}