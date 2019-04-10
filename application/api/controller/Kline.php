<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * Kline接口
 */
class Kline extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 获取日K
     * 
     */
    public function index()
    {
        $myset= config('site');
        $day = $myset['kday'];          //周期天数
        $startp =  $myset['startp'];   //发行价
        $chu =  $myset['kbei'];     //出局倍数
        $kaipan =  $myset['kopen'];  //开盘时间
        //幅度
        $k=round(($chu*$startp-$startp)/$day,3);
        $value=  rand(1111,9999);
//
//        $j = time()-$day*86400;
//        for ($i = 0; $i < $day; $i++) {
//
////            $k=round($this->randFloat($myset['fumin'],$myset['fumax']),2);
//
//            $d = $j+$i*86400;
//            $time[] = date('Y-m-d',$d);
//             $value=  rand(1111,9999);
//             //OPEN CLOSE LOW HIGH VALUE
//            $data[] = [$startp +($i-1) * $k,$startp +$i * $k, $startp + ($i-2) * $k, $startp + ($i+1) * $k,$value];
//
//            $volumes[] = $value;
//        }

        $day= db('kline');
        $now = date('Y-m-d', time());
        $re = $day->where(['time' => $now])->find();
        if (!$re) {
            $cha =(strtotime($now)-strtotime($kaipan))/86400 ;     //相差天数
            $data= [
                'open'=>  round($startp +($cha-1) * $k+mt_rand() / mt_getrandmax()*1*$k,3),
                'close'=> round($startp +$cha * $k+mt_rand() / mt_getrandmax()*3*$k,3),
                'low'=> round($startp + ($cha-2) * $k+mt_rand() / mt_getrandmax()*3*$k,3),
                'high'=>  round($startp + ($cha+1) * $k+mt_rand() / mt_getrandmax()*3*$k,3),
                'value'=> $value,
                'time' => $now
            ];
            $day->insert($data);
        }

        $info =  $day->order('id')->select();
//        dump($info);
        if($info){
            foreach ($info as $item){
                $time[] =$item['time'];
                $data[] =[
                    $item['time'],
                    (float)$item['open'],
                    (float)$item['close'],
                    (float)$item['low'],
                    (float)$item['high']
                ];
                $volumes[] =$item['value'];
            }
        }

        $this->success('',array('time' => $time, 'data' =>$data , 'volumes' => $volumes));
    }

      /**
     * 黄金分时
     * @return bool|string
     */
    public function getau()
    {
        $date=date('YmdH',time()+96400);
        $re=file_get_contents('http://webforex.hermes.hexun.com/forex/kline?code=FOREXXAUUSD&start='.$date.'0000&number=-960&type=0');
        $re = trim($re,'(');
        $re = trim($re,');');
        $re=json_decode($re,true);
        foreach ($re['Data'][0] as $item){
            $data[] =[
                $item[0]*0.01,
                (float)$item[2],
                round($item[1]*0.0001,4),
                (float)$item[2],
                (float)$item[2],
            ];
        }
        $end = end($data);
        $re1=[
            'date'=>$date,
            'nowprice'=>$end[2],
            'data'=>$data
        ];
        return json($re1);
    }


}
