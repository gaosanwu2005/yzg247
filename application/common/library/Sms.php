<?php

namespace app\common\library;

use think\Hook;
use think\Log;

/**
 * 短信验证码类.
 */
class Sms
{
    /**
     * 验证码有效时长
     *
     * @var int
     */
    protected static $expire = 120;

    /**
     * 最大允许检测的次数.
     *
     * @var int
     */
    protected static $maxCheckNums = 10;

    /**
     * 获取最后一次手机发送的数据.
     *
     * @param int    $mobile 手机号
     * @param string $event  事件
     *
     * @return Sms
     */
    public static function get($mobile, $event = 'default')
    {
        $sms = \app\common\model\Sms::
        where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        Hook::listen('sms_get', $sms, null, true);

        return $sms ? $sms : null;
    }

    /**
     * 发送验证码
     *
     * @param int    $mobile 手机号
     * @param int    $code   验证码,为空时将自动生成4位数字
     * @param string $event  事件
     *
     * @return bool
     */
//    public static function send($mobile, $code = NULL, $event = 'default')
//    {
//        $code = is_null($code) ? mt_rand(1000, 9999) : $code;
//        $time = time();
//        $ip = request()->ip();
//        $sms = \app\common\model\Sms::create(['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]);
//        $result = Hook::listen('sms_send', $sms, null, true);
//        if (!$result)
//        {
//            $sms->delete();
//            return FALSE;
//        }
//        return TRUE;
//    }

    /**
     * 发送验证码
     *
     * @param int    $userid
     * @param string $type
     * @param string $argu1
     * @param string $argu2
     *
     * @return bool
     */
    public static function send($mobile, $type = '0', $event = 'default', $argu1 = '', $argu2 = '')
    {
        $site = \think\Config::get('site');
        $apikey = $site['apikey'];
        $account = $site['account'];
        if (strpos($type, '3') !==false) {
            switch ($type) {
                case '3_1':
                    $code = '安监局';
                    break;
                // no break
                case '3_2':
                    $code = '质监局';
                    break;
                // no break
                case '3_3':
                    $code = '人保部';
                    break;
                default:
                    $code = '安监局';
                    break;
            }
        } else {
            $code = mt_rand(1000, 9999);
        }
        if ($type == '0') {
            $content = '您的验证码是'.$code;
        } elseif ($type == '1') {
            $content = '您正在重置登录密码和交易密码，您的验证码是'.$code.'，切勿将验证码告诉他人';
        } elseif ($type == '2') {
            $content = '您正在重置交易密码，您的验证码是'.$code.'，切勿将验证码告诉他人';
        } elseif ($type == '3') {
            $content = '尊敬的用户您好，您申请的'.$code.'报名已经审核通过，请登录查看。';
        } else {
            $content = $site[$type];
        }
        $n1 = substr($argu1, 0, 5);
        $n2 = substr($argu2, 0, 5);
        if ($n1) {
            $content = str_replace('@', $n1, $content);
        }
        if ($n2) {
            $content = str_replace('#', $n2, $content);
        }
        $text = $site['signature'].$content;
//        \Think\Log::record('type-------------' . $type);
//        \Think\Log::record('apikey-------------' . $apikey);
//        \Think\Log::record('手机-------------' . $mobile);
//        \Think\Log::record('发送内容-------------' . $content);
//        // 发送短信
//        $data = array('text' => $text, 'apikey' => $apikey, 'mobile' => $mobile);
//        curl_setopt($ch, CURLOPT_URL, 'http://yunpian.com/v1/sms/send.json');
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        $json_data = curl_exec($ch);
//        $res = json_decode($json_data, true);
//        \Think\Log::record('云片反馈-------------' . serialize($res));
//        if ($res['code'] != 0) {
//            return false;
//        } else {
//            if( $type ==0){
//                $time = time();
//                $ip = request()->ip();
//                \app\common\model\Sms::create(['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]);
//            }
//            return true;
//        }
        $url = 'http://dx.ipyy.net/sms.aspx?action=send&userid=&account='.$account.'&password='.$apikey.'&mobile='.$mobile.'&content='.$text;
        \Think\Log::record('-------------url'.$url);
        file_put_contents('1.txt', $url);
        //初始化curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //post提交方式
        //运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        $r = simplexml_load_string($data);
        \Think\Log::record($r);
        $f = (int) $r->successCounts;
        if ($f == 1) {
            if ($type == 0) {
                $time = time();
                $ip = request()->ip();
                \app\common\model\Sms::create(['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]);
            }

            return true;
        } else {
            return false;
        }
    }

    public static function send1086($mobile, $type = 0)
    {
        $account = '19937689493';
        $apikey = '19937689493abcd';

        $timestamp = date('Y-m-d H:i:s');
        $code = mt_rand(1000, 9999);
        if ($type == 0) {
            // $content = '您的验证码是'.$code.'【测试】';
            $content = '您的求购订单，已成功预定等待您的付款。'.'【测试】';
        }
        $get = ['username' => $account, 'password' => md5($apikey.$timestamp), 'mobiles' => $mobile, 'content' => $content, 'timestamp' => $timestamp];
        $option = http_build_query($get);
        $url = 'http://api.sms1086.com/api/Sendutf8.aspx?'.$option;
        \Think\Log::record('-------------url'.$url);
        file_put_contents('1.txt', $url);
        //初始化curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        file_put_contents('2.txt', $data);
    }

    /**
     * 发送通知.
     *
     * @param mixed  $mobile   手机号,多个以,分隔
     * @param string $msg      消息内容
     * @param string $template 消息模板
     *
     * @return bool
     */
    public static function notice($mobile, $msg = '', $template = null)
    {
        $params = [
            'mobile' => $mobile,
            'msg' => $msg,
            'template' => $template,
        ];
        $result = Hook::listen('sms_notice', $params, null, true);

        return $result ? true : false;
    }

    /**
     * 校验验证码
     *
     * @param int    $mobile 手机号
     * @param int    $code   验证码
     * @param string $event  事件
     *
     * @return bool
     */
    public static function check($mobile, $code, $event = 'default')
    {
        $site = \think\Config::get('site');
        $youxiao = $site['expire'] ? $site['expire'] : self::$expire;
        $maxcheck = $site['maxCheckNums'] ? $site['maxCheckNums'] : self::$maxCheckNums;
        $time = time() - $youxiao;
        $sms = \app\common\model\Sms::where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        if ($sms) {
            if ($sms['createtime'] > $time && $sms['times'] <= $maxcheck) {
                $correct = $code == $sms['code'];

                if (!$correct) {
                    $sms->times = $sms->times + 1;
                    $sms->save();

                    return false;
                } else {
                    return true;
//                    $result = Hook::listen('sms_check', $sms, null, true);
//                    return $result;
                }
            } else {
                // 过期则清空该手机验证码
                self::flush($mobile, $event);

                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 清空指定手机号验证码
     *
     * @param int    $mobile 手机号
     * @param string $event  事件
     *
     * @return bool
     */
    public static function flush($mobile, $event = 'default')
    {
        \app\common\model\Sms::
        where(['mobile' => $mobile, 'event' => $event])
            ->delete();
        Hook::listen('sms_flush');

        return true;
    }

    /**
     * @return mixed
     */
    public static function getSite()
    {
        return self::$site;
    }

    /**
     *聚合短信接口.
     */
    public static function jhsend($mobile, $type = '0', $event = 'default', $argu1 = '', $argu2 = '')
    {
        $code = mt_rand(1000, 9999);
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        $smsid = 117160;
        if ($type == '0') {
            $content = '您的验证码是#code#。如非本人操作，请忽略本短信';
            $smsid = 117160;
        } elseif ($type == '1') {
            $content = '您正在找回密码，您的验证码是#code#';
            $smsid = 117158;
        } elseif ($type == '2') {
            $content = '您正在找回密码，您的验证码是#code#';
            $smsid = 117158;
        } elseif ($type == '3') {
            $content = '您正在进行身份认证，您的验证码是#code#。如非本人操作，请忽略本短信';
            $smsid = 117159;
        } elseif ($type == '4') {
            $content = '您正在登录，您的验证码是#code#。如非本人操作，请忽略本短信';
            $smsid = 117157;
        } elseif ($type == '5') {
            $content = '您正在注册，您的验证码是#code#。如非本人操作，请忽略本短信';
            $smsid = 117156;
        } elseif ($type == '6') {
            $content = '您的求购订单，已成功预定等待您的付款。';
            $smsid = 118481;
        } elseif ($type == '7') {
            $content = '您的订单，对方已成功付款等待您的确认。';
            $smsid = 118480;
        }
        $smsConf = array(
            'key' => '1f5335a96b422bd47bd23682bfb1cf3f', //您申请的APPKEY
            'mobile' => $mobile, //接受短信的用户手机号码
            'tpl_id' => $smsid, //您申请的短信模板ID，根据实际情况修改
            'tpl_value' => '#code#='.$code, //您设置的模板变量，根据实际情况修改
        );

        $content = juhecurl($sendUrl, $smsConf, 1); //请求发送短信
        if ($content) {
            $result = json_decode($content, true);
            $error_code = $result['error_code'];
            if ($error_code == 0) {
                //状态为0，说明短信发送成功
//                echo "短信发送成功,短信ID：".$result['result']['sid'];
                $time = time();
                $ip = request()->ip();
                \app\common\model\Sms::create(['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]);

                return true;
            } else {
                //状态非0，说明失败
                $msg = $result['reason'];
//                echo "短信发送失败(".$error_code.")：".$msg;
                Log::info($msg);

                return false;
            }
        } else {
            //返回内容异常，以下可根据业务逻辑自行修改
//            echo "请求发送短信失败";
            return false;
        }
    }
}
