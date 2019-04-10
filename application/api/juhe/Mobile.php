<?php

namespace app\api\juhe;

trait Mobile{

    use Util;

    private $mobile_openid = 'JHf3cca0c303d1623e2d8e98e9471d4f17';
    private $mobile_appkey = '53c63fc8446c2a69f55d076f59ac094b';

    private $mobile_CheckUrl = 'http://op.juhe.cn/ofpay/mobile/telcheck';
    private $mobile_QueryUrl = 'http://op.juhe.cn/ofpay/mobile/telquery';
    private $mobile_submitUrl = 'http://op.juhe.cn/ofpay/mobile/onlineorder';
    private $mobile_staUrl = 'http://op.juhe.cn/ofpay/mobile/ordersta';

    /**
     * 根据手机号码及面额查询是否支持充值
     * @param  string $mobile   [手机号码]
     * @param  int $pervalue [充值金额]
     * @return  boolean
     */
    private function mobilecheck($mobile,$pervalue){
        $params = 'key='.$this->mobile_appkey.'&phoneno='.$mobile.'&cardnum='.$pervalue;
        $content = $this->juhecurl($this->mobile_CheckUrl,$params);
        $result = $this->_returnArray($content);
        if($result['error_code'] == '0'){
            return true;
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
    private function mobilequery($mobile,$pervalue){
        $params = 'key='.$this->mobile_appkey.'&phoneno='.$mobile.'&cardnum='.$pervalue;
        $content = $this->juhecurl($this->mobile_QueryUrl,$params);
        return $this->_returnArray($content);
    }

    /**
     * 提交话费充值
     * @param  [string] $mobile   [手机号码]
     * @param  [int] $pervalue [充值面额]
     * @param  [string] $orderid  [自定义单号]
     * @return  [array]
     */
    private function mobilecz($mobile,$pervalue,$orderid){
        $sign = md5($this->mobile_openid.$this->mobile_appkey.$mobile.$pervalue.$orderid);//校验值计算
        $params = array(
            'key' => $this->mobile_appkey,
            'phoneno'   => $mobile,
            'cardnum'   => $pervalue,
            'orderid'   => $orderid,
            'sign' => $sign
        );
        $content = $this->juhecurl($this->mobile_submitUrl,$params,1);
        return $this->_returnArray($content);
    }

    /**
     * 查询订单的充值状态
     * @param  [string] $orderid [自定义单号]
     * @return  [array]
     */
    private function mobilestatus($orderid){
        $params = 'key='.$this->mobile_appkey.'&orderid='.$orderid;
        $content = $this->juhecurl($this->mobile_staUrl,$params);
        return $this->_returnArray($content);
    }

}