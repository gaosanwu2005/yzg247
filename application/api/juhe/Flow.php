<?php
namespace app\api\juhe;

trait Flow{

    private $flow_openid = 'JHf3cca0c303d1623e2d8e98e9471d4f17';
    private $flow_appkey = '57719f74d44e49ef180c76edfa88f7f2';

    private $flow_CheckUrl = 'http://v.juhe.cn/flow/telcheck';
    private $flow_QueryUrl = ' http://v.juhe.cn/flow/list';
    private $flow_submitUrl = 'http://v.juhe.cn/flow/recharge';
    private $flow_staUrl = 'http://v.juhe.cn/flow/ordersta';


    /**
     * 检测号码支持的流量套餐
     * @param  string $mobile   [手机号码]
     * @return  boolean
     */
    private function flowcheck($mobile, $value){
        $params = 'key='.$this->flow_appkey.'&phone='.$mobile;
        $content = $this->juhecurl($this->flow_CheckUrl,$params);
        $result = $this->_returnArray($content);
        if($result['error_code'] == '0'){
            foreach ($result['result'][0]['flows'] as $key=>$item){
                if ($value == $item['v']){
                    return $item;
                }
            }
            return false;
        }else{
            return false;
        }
    }

    /**
     * 根据手机号码和面额获取商品信息
     * @param  string $mobile   [手机号码]
     * @param  int $pervalue [充值金额]
     * @return  array
     */
    private function flowquery($mobile,$pervalue){
        $params = 'key='.$this->flow_appkey.'&phoneno='.$mobile.'&cardnum='.$pervalue;
        $content = $this->juhecurl($this->flow_QueryUrl,$params);
        return $this->_returnArray($content);
    }


    /**
     * 提交流量充值
     * @param  [string] $mobile   [手机号码]
     * @param  [int] $pervalue [充值面额]
     * @param  [string] $orderid  [自定义单号]
     * @return  [array]
     */
    private function flowcz($mobile, $pid, $orderid){
        $sign = md5($this->flow_openid.$this->flow_appkey.$mobile.$pid.$orderid);//校验值计算
        $params = array(
            'phone'   => $mobile,
            'pid'   => $pid,
            'orderid'   => $orderid,
            'key' => $this->flow_appkey,
            'sign' => $sign
        );
        $content = $this->juhecurl($this->flow_submitUrl,$params,1);
        return $this->_returnArray($content);
    }

    /**
     * 查询订单状态
     * @param  [string] $orderid [自定义单号]
     * @return  [array]
     */
    private function flowsta($orderid){
        $params = 'key='.$this->flow_appkey.'&orderid='.$orderid;
        $content = $this->juhecurl($this->flow_staUrl,$params);
        return $this->_returnArray($content);
    }

}