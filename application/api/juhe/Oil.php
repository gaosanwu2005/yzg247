<?php
namespace app\api\juhe;


trait Oil{
    private $oil_openid = 'JHf3cca0c303d1623e2d8e98e9471d4f17';
    private $oil_appkey = '51d28bfbd8df042da206627ca4ae379e';
    private $oil_yue = 'http://op.juhe.cn/ofpay/sinopec/yue';
    private $oil_submitUrl = 'http://op.juhe.cn/ofpay/sinopec/onlineorder';
    private $oil_staUrl = 'http://op.juhe.cn/ofpay/sinopec/ordersta';



    //订单状态查询
    private function oilstatus($orderId)
    {
        $params = [
            "orderid" => $orderId,
            "key" => $this->oil_appkey
        ];
        $paramstring = http_build_query($params);
        $content = $this->juhecurl($this->oil_status,$paramstring);
        $result = $this->_returnArray($content);
        return $result;
    }

    //账户余额查询
    public function oilyue()
    {
        $params = [
            "timestamp" => time(),//当前时间戳，如：1432788379
            "key" => $this->oil_appkey,//应用APPKEY(应用详细页查询)
            "sign" => md5($this->oil_openid.$this->oil_appkey.time()),//校验值，md5(OpenID+key+timestamp)，OpenID在个人中心查询
        ];
        $paramstring = http_build_query($params);
        $content = juhecurl($this->oil_yue,$paramstring);
        $result = json_decode($content,true);
        return $result;
    }

    //加油卡充值
    public function oilcz($proid, $cardnum, $orderId, $game_userid, $gasCardName)
    {
        $params = [
            "proid" => $proid,//产品id:10000(中石化50元加油卡)[暂不支持]、10001(中石化100元加油卡)、10003(中石化500元加油卡)、10004(中石化1000元加油卡)、10007(中石化任意金额充值)[暂不支持]、10008(中石油任意金额充值)
            "cardnum" => '1',//充值数量（产品id为10007、10008时为具体充值金额(整数)，其余产品id请传固定值1）；注：中石油任意冲(产品id:10008)暂时只支持100\200\500\1000
            "orderid" => $orderId,//商家订单号，8-32位字母数字组合
            "game_userid" => $game_userid,//加油卡卡号，中石化：以100011开头的卡号、中石油：以9开头的卡号
            "gasCardTel" => "18900000000",//持卡人手机号码,可以填写一个固定格式的手机号码，如:18900000000
            "gasCardName" => $gasCardName,//持卡人姓名
            "chargeType" => "1",//加油卡类型 （1:中石化、2:中石油；默认为1)
            "key" => $this->oil_appkey,//应用APPKEY(应用详细页查询)
            "sign" => md5($this->oil_openid.$this->oil_appkey.$proid.$cardnum.$game_userid.$orderId),//校验值，md5(OpenID+key+proid+cardnum+game_userid+orderid)，OpenID在个人中心查询
        ];
        $paramstring = http_build_query($params);
        $content = $this->juhecurl($this->oil_submitUrl,$paramstring);
        $result = $this->_returnArray($content);
        return $result;
    }

}