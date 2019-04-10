<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use app\common\library\Sms;
use think\exception\DbException;

/**
 * 虚拟币交易接口
 */
class Trade extends Api
{

    protected $noNeedLogin = ['index'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 奖金转账
     * @param string $toaccount 转入账号
     * @param string $price 转入金额
     * @param string $password 交易密码
     * @param string $ptype 钱包类型
     */
    public function pdzz()
    {
        $data = input('post.');
        $phone = $this->request->request('phone');
        $captcha = $this->request->request('captcha');
        $toaccount = input('post.walladress');
        $price = input('post.price');
        $walltype = input('post.ptype');
        $password = input('post.password');
        $rshy = $this->auth->getUserinfo();
        $myset = config('site');
        if (authcode($rshy['password2'], 'DECODE') != $password) {
            $this->error('您输入的交易密码错误' . $password);
        }
        if (!Sms::check($phone, $captcha, 'c2c')) {
            $this->error(__('验证码不正确'));
        }
        if ($price <= 0) {
            $this->error('请输入正确的金额!');
        }
        if ($rshy['slrate'] < $myset['c2csl'] ) {
            $this->error('算力大于' . $myset['c2csl'] . 'G可以交易,你的算力是' . $rshy['slrate'] . 'G');
        }
        $c2cfee = $price*$myset['c2cfee']*0.01;
        if ($rshy[$walltype] < $price+$c2cfee) {
            $this->error('您的余额不足！' . $rshy[$walltype]);
        }
        $rsjs = Db::name('user')->where(array('walladress' => $toaccount))->find();

        if (!$rsjs) {
            $this->error('未找到接受的会员');
        }
//        if (strpos($rsjs['tpath'], (string)$rshy['id']) === false && $walltype=='wall1') {
//            $this->error('只能给自己的团队转账源币');
//        }
        if (strpos($rsjs['tpath'], (string)$rshy['id']) === false && strpos($rshy['tpath'], (string)$rsjs['id']) === false && $walltype == 'wall1') {
            $this->error('只能给上下级转账源币');
        }
        if ($rsjs['username'] == $rshy['username']) {
            $this->error('不能给自己转账！');
        }
        if($walltype=='wall2' && $rshy['wall7']<$price){
            $this->error('您的授信额度不足！' . $rshy['wall7']);
        }
        if($walltype=='wall2' && $rshy['v1'] < ceil($price/50)){
            $this->error('令牌不足');
        }

        $rs = caiwu($rshy['id'], -$price, 7, $walltype, "转账给" . $rsjs['username']);
        if($walltype=='wall2'){
            $needv1 = ceil($price/50);
            caiwu($rshy['id'], -$price, 7, 'wall7', "转账给" . $rsjs['username']);
            caiwu($rshy['id'], -$needv1, 7, 'v1', "转账给" . $rsjs['username']);
            caiwu($rsjs['id'], $data['price']*1.7, 7, 'wall7', "收到" . $rshy['username'] . '的转账');
        }
        if($c2cfee>0  && $walltype=='wall1') caiwu($rshy['id'], -$c2cfee, 7, $walltype, "转账给" . $rsjs['username'].'手续费');
        if ($rs) {
            $rs2 = caiwu($rsjs['id'], $data['price'], 7, $walltype, "收到" . $rshy['username'] . '的转账');

            if ($rs2) {
                $this->success('转账成功！');
            } else {
                $this->error('转账失败！');
            }
        } else {
            $this->error('转账失败！');
        }


    }

    /**
     * 购买矿机
     * @param string $gid 矿机id
     */
    public function gmkj()
    {

        $id = $this->request->request('gid');
        $ptype = $this->request->request('ptype');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();


            $goods = Db::name('goods')->where(array('goods_id' => $id))->cache(true, 300)->find();
            if ($goods['store_count'] == 0) {
                $this->error('库存不足');
            }
            $goods_type_keywords = explode("云", $goods['goods_name']);
            if ($user['slrate'] < 90 && in_array($goods_type_keywords[0], ['大型'])) {
                $this->error('您的算力不足90G');
            }

            if ($user['slrate'] < 180 && in_array($goods_type_keywords[0], ['巨型', '超级巨型'])) {
                $this->error('您的算力不足180G');
            }

            if (!$user['idcard'] || !$user['banknum'] || !$user['alipay']) {
                $this->error("请完善个人信息", '', url('index/user/realname'));
            }
            if ($goods['xian_price'] > $user[$ptype]) {
                $this->error('您的余额不足' . $user[$ptype]);
            }
            if($ptype=='wall2' && $goods['xian_price'] > $user['wall7']){
                $this->error('您的授信额度不足！' . $user['wall7']);
            }
            $num = Db::name('order')->where(array('uid' => $user['id'], 'gid' => $id, 'iszs' => 0))->count() + 0;//购买此矿机数量
            if ($num >= $goods['xg']) {
                $this->error('每人只可购买' . $goods['xg'] . '台' . $goods['goods_name']);
            }
            caiwu($user['id'], -$goods['xian_price'], 5, $ptype, '购买矿机' . $goods['goods_name']);
            if($ptype=='wall2'){
                caiwu($user['id'], -$goods['xian_price'], 5, 'wall7', '购买矿机' . $goods['goods_name']);
            }
            //上级奖励10% 交易额度
            caiwu($user['tjid'], $goods['xian_price']*0.1, 5, 'wall7', '下级购买矿机' . $goods['goods_name']);
            $result = addMine($user['id'], $user['username'], $id, 0);
            if ($result) {
                Db::name('goods')->where(array('goods_id' => $id))->setDec('store_count', 1);
                Db::commit();
                $this->success('购买成功', '', url('index/user/mills'));
            } else {
                Db::rollback();
                $this->error('购买失败');
            }
        } catch (Exception $e) {
            Db::rollback();
            $this->success('购买失败');
        }

    }


    //公共检查
    private function comcheck($btc, $cny, $type = 'buy')
    {
//        $password = input('post.password2');
        $myset = config('site');
        $rshy = $this->auth->getUserinfo();
        if (!$cny) $this->error('请输入价格');
        if ($btc <= 0) {
            $this->error('请输入正确的金额');
        }
        if ($rshy['jointime'] == 0) {
            $this->error('请激活账号再操作！', '', '');
        }
        if ($rshy['issm'] == '0') {
            $this->error('请先进行身份认证！', '', url('index/user/realname'));
        }
        if (date('H') < $myset['opentime'] || date('H') >= $myset['closetime']) {
            $this->error("开放时间{$myset['opentime']}点--{$myset['closetime']}点");
        }

//        if ($btc < $myset['ntbmin'] || $btc > $myset['ntbmax']) {
//            $this->error("交易额度必须在{$myset['ntbmin']} -{$myset['ntbmax']}以内！");
//        }
//        if (authcode($rshy['password2'], 'DECODE') != $password) {
//            $this->error('您输入的交易密码错误');
//        }
        if ($type == 'buy') {
//            $Tgmx = new \app\admin\model\Tgmx();
//            $lastg = $Tgmx->where(['userid' => $rshy['id'], 'status' => ['in',[0,5]]])->order('addtime desc')->find();
//            if ($btc < $lastg['number']) {
//                $this->error('复投金额不能低于上轮投资额度');
//            }
//            if ($lastg && (time() - $lastg['addtime']) < $myset['pdspace'] * 86400) {
//                $this->error("两次交易的间隔不能小于{$myset['pdspace']}天");
//            }
            $all = $btc * $cny;
            $fee = round($all * $myset['buyfee'] * 0.01, 2);
//            $max = $rshy['wall3'] / ($myset['buyfee'] * 0.01 + 1) / $cny;
//            $trade = $all * ($myset['buyfee'] * 0.01 + 1);

//            if ($btc > $max) {
//                $this->error('购买数量超过最大购买');
//            }


            if ($rshy['slrate'] < $myset['buysl'] && $btc > $myset['rzbuy']) {
                $this->error('算力大于' . $myset['buysl'] . 'G可以求购0-' . $myset['maxbuysl'] . ',你的算力是' . $rshy['slrate'] . 'G');
            }
            if ($rshy['slrate'] > $myset['buysl'] && $btc > $myset['maxbuysl']) {
                $this->error('您最多能购买'.$myset['maxbuysl'].'个');
            }
        } else {
            $fee = round($btc * $myset['salefee'] * 0.01, 4);
            if ($rshy['slrate'] < $myset['salesl']) {
                $this->error('算力达到' . $myset['salesl'] . 'G,卖方才可以交易,你的算力是' . $rshy['slrate'] . 'G');
            }
            if ($fee+$btc > $rshy['wall2']) {
                $this->error('LMC额度不足');
            }
            if($btc > $rshy['wall7']){
                $this->error('授信额度不足，您的授信额度为'.$rshy['wall7']);
            }
            if($rshy['v1'] < ceil($btc/50)){
                $this->error('令牌不足');
            }

        }

        return $fee;
    }

//    指定匹配
    public function clicksale()
    {
        $tgid = input('id', 0);
        if (!$tgid) {
            $this->error('订单不存在');
        }
        $Tgmx = new \app\admin\model\Tgmx();
        $Xymx = new \app\admin\model\Xymx();
        $Ppmx = new \app\common\model\Ppmx();
        $user = $this->auth->getUserinfo();
        $buy = $Tgmx->where(['tgid' => $tgid, 'status' => 0])->find();
        if (!$buy) {
            $this->error('订单不存在');
        }
        if ($buy['userid'] == $user['id']) {
            $this->error('不能跟自己交易');
        }
        $number = $buy['number'] - $buy['buy_number'];
        $fee = $this->comcheck($number, $buy['price'], 'sale');
        //增加卖出记录
        $Ppmx->sale($number, $buy['price'], $fee, $user);
        $saleid = $Ppmx->btc_sale_id;
        $sale = $Xymx->where(['xyid' => $saleid, 'status' => '0'])->find();
        //匹配金额记录
        $dd = $Ppmx->trade($number, $sale, $buy, 5);
        if ($dd) {
//            smscode(session('uid'),1,session('username'));
//            smscode($buy['userid'],1,$buy['account']);
            $this->success('提交成功', '321');
        } else {
            $this->error('提交失败，请稍后再试');
        }

    }

//    指定匹配
    public function clickbuy()
    {
        $xyid = input('id', 0);
        if (!$xyid) {
            $this->error('订单不存在');
        }
        $Tgmx = new \app\admin\model\Tgmx();
        $Xymx = new \app\admin\model\Xymx();
        $Ppmx = new \app\common\model\Ppmx();
        $user = $this->auth->getUserinfo();
        $sale = $Xymx->where(['xyid' => $xyid, 'status' => 0])->find();
        if (!$sale) {
            $this->error('订单不存在');
        }
        if ($sale['userid'] == $user['id']) {
            $this->error('不能跟自己交易');
        }
        $number = $sale['number'] - $sale['sale_number'];
        $fee = $this->comcheck($number, $sale['price'], 'buy');
        //增加购买记录
        $Ppmx->buy($number, $sale['price'], $fee, $user);
        $buyid = $Ppmx->btc_buy_id;
        $buy = $Tgmx->where(['tgid' => $buyid, 'status' => 0])->find();
        //增加匹配记录
        $dd = $Ppmx->trade($number, $sale, $buy, 4);
        if ($dd) {
//            smscode(session('uid'),1,session('username'));
//            smscode($sale['userid'],1,$sale['account']);
            $this->success('提交成功', '321');
        } else {
            $this->error('提交失败，请稍后再试');
        }

    }

    //自动匹配
    public function buy()
    {
        $user = $this->auth->getUserinfo();

        $btc = input('btc', 0);     //数量
        $cny = input('cny', 1);     //单价

        $fee = $this->comcheck($btc, $cny, 'buy');
        $Ppmx = new \app\common\model\Ppmx();
        if ($Ppmx->buy($btc, $cny, $fee, $user)) {
            // 自动交易
//            if ($Ppmx->findSale($Ppmx->btc_buy_id)) {
//                $data['info'] = '交易中……，请稍后到已完成查看';
//            }
            $this->success('提交成功', '321');
        } else {
            $this->error($Ppmx->getError());
        }
    }

    //自动匹配
    public function sale()
    {
        $user = $this->auth->getUserinfo();
        $btc = input('param.btc', 0);     //数量
        $cny = input('param.cny', 1);     //单价
        $fee = $this->comcheck($btc, $cny, 'sale');
        $Ppmx = new \app\common\model\Ppmx();
        if ($Ppmx->sale($btc, $cny, $fee, $user)) {
            // 自动交易
//            if ($Ppmx->findbuy($Ppmx->btc_sale_id)) {
//                $data['info'] = '交易中……，请稍后到已完成查看';
//            }
            $this->success('提交成功', '321');
        } else {
            $this->error($Ppmx->getError());
        }
    }

    /**
     * 撤销卖单
     * @param string $sale 卖单编号
     * @return \think\response\Json
     */
    public function CancelSaleOrder()
    {

        $id = input('sale');
        $user = $this->auth->getUserinfo();
        $Ppmx = new \app\common\model\Ppmx();
        if ($Ppmx->cancelSaleOrder($user['id'], $id)) {
            $this->success('撤单成功', 321);
        } else {
            $this->error($Ppmx->getError());
        }
    }

    /**
     * 撤销买单
     * @param string $buy 买单编号
     * @return \think\response\Json
     */
    public function cancelBuyOrder()
    {

        $id = input('buy');
        $user = $this->auth->getUserinfo();
        $Ppmx = new \app\common\model\Ppmx();
        if ($Ppmx->cancelBuyOrder($user['id'], $id)) {
            $this->success('撤单成功', 321);
        } else {
            $this->error($Ppmx->getError());
        }


    }

    /**
     * 列表行情
     */
    public function hangqing()
    {
        $user = $this->auth->getUserinfo();
        $Tgmx = Db::name('Tgmx');
        $Xymx = Db::name('Xymx');
        $Ppmx = Db::name('Ppmx');
        $myset = config('site');
        $buylist = $Tgmx->where('status', 0)->order('price DESC')->limit(10)->select();
        $salelist = $Xymx->where('status', 0)->order('price ASC')->limit(10)->select();

        $map['userid'] = $user['id'];
        $map['status'] = 0;
        $map2['userid|userid1'] = $user['id'];
        $map2['status'] = ['in', [0, 1, 3, 4]];
        $buyorder = $Tgmx->where($map)->order('tgid DESC')->limit(20)->select();
        $saleorder = $Xymx->where($map)->order('xyid DESC')->limit(20)->select();
        $tradeorder = $Ppmx->where($map2)->order('ppid DESC')->limit(10)->select();

        if ($buylist) {
            foreach ($buylist as $key => $item) {
                $buylist[$key]['addtime'] = date('Y-m-d H:i', $item['addtime']);
                $buylist[$key]['num'] = $item['number'] - $item['buy_number'];
                $buylist[$key]['total'] = round($buylist[$key]['num'] * $item['price'], 2);
                $buylist[$key]['buy_total'] = round($item['buy_number'] * $item['price'], 2);
            }
        }
        if ($salelist) {
            foreach ($salelist as $key2 => $item2) {
                $salelist[$key2]['addtime'] = date('Y-m-d H:i', $item2['addtime']);
                $salelist[$key2]['num'] = $item2['number'] - $item2['sale_number'];
                $salelist[$key2]['total'] = round($salelist[$key2]['num'] * $item2['price'], 2);
            }
        }
        if ($buyorder) {
            foreach ($buyorder as $key3 => $item3) {
                $buyorder[$key3]['addtime'] = date('Y-m-d H:i', $item3['addtime']);
            }
        }
        if ($saleorder) {
            foreach ($saleorder as $key4 => $item4) {
                $saleorder[$key4]['addtime'] = date('Y-m-d H:i', $item4['addtime']);
            }
        }
        if ($tradeorder) {


            foreach ($tradeorder as $key5 => $item5) {
                $end1 = $item5['addtime'] + $myset['zdqx'] * 3600;
                $end2 = $item5['paytime'] + $myset['zdqr'] * 3600;
                //卖家
                $tmp = [0 => '<a style="color: red;" href="' . url('index/business/dkdetail') . '?ppid=' . $item5['ppid'] . '" >买家待付款</a>',
                        1 => '<a  class="button button-round active" href="' . url('index/business/dkdetail') . '?ppid=' . $item5['ppid'] . '" >买家已付款待确认</a>',
                        2 => '已完成',
                        3 => '已取消',
                        4 => '投诉中'];
                //买入
                $tmp2 = [0 => '<a  class="button button-round active" href="' . url('index/business/skdetail') . '?ppid=' . $item5['ppid'] . '" >点击付款</a>',
                         1 => '<a style="color: green;" href="' . url('index/business/skdetail') . '?ppid=' . $item5['ppid'] . '" >已付款待对方确认</a>',
                         2 => '已完成',
                         3 => '已取消',
                         4 => '投诉中'];
                //买入
                $tmp3 = [0 => '<span >剩余打款时间:</span><span style="font-size: smaller;" class="untime" data-time="' . $end1 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         1 => '<span >剩余确认时间:</span><span style="font-size: smaller;" class="toutime" data-time="' . $end2 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         2 => '',
                         3 => '',
                         4 => ''];
                //卖出
                $tmp4 = [0 => '<span >剩余打款时间:</span><span style="font-size: smaller;" class="toutime" data-time="' . $end1 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         1 => '<span >剩余确认时间:</span><span style="font-size: smaller;" class="untime" data-time="' . $end2 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         2 => '',
                         3 => '',
                         4 => ''];
                if ($item5['userid'] == $user['id']) {
                    $tradeorder[$key5]['type'] = '买入';
                    $tradeorder[$key5]['status'] = $tmp2[$item5['status']];
                    $tradeorder[$key5]['endtime'] = $tmp3[$item5['status']];
                } else {
                    $tradeorder[$key5]['type'] = '卖出';
                    $tradeorder[$key5]['status'] = $tmp[$item5['status']];
                    $tradeorder[$key5]['endtime'] = $tmp4[$item5['status']];
                }
                $tradeorder[$key5]['addtime'] = date('m-d H:s', $item5['addtime']);

            }
        }
        $data['buyorder'] = $buyorder;  //个人买
        $data['saleorder'] = $saleorder;  //个人卖
        $data['tradeorder'] = $tradeorder;  //个人交易

        $data['buy'] = $buylist;
        $data['sale'] = $salelist;
//        $data['tradelist'] = $tradelist;
        return json($data);
    }


    /**
     * 获取更多卖单
     * @param number $from 开始
     * @param number $to 结束
     */
    public function saleajax()
    {
        $from = input('from', 0);
        $to = input('to', 1);
        $Xymx = new \app\admin\model\Xymx();
        $map['status'] = 0;
        $salelist = $Xymx->where($map)->order('number ASC')->limit($from, $to)->select();
        if ($salelist) {
            foreach ($salelist as $key2 => $item2) {
                $salelist[$key2]['addtime'] = date('m-d H:s', $item2['addtime']);
                $salelist[$key2]['num'] = $item2['number'] - $item2['sale_number'];
            }
        }
        $data['sale'] = $salelist;
        return json($data);
    }

    /**
     * 获取更多买单
     * @param number $from 开始
     * @param number $to 结束
     */
    public function buyajax()
    {
        $from = input('from', 0);
        $to = input('to', 1);
        $Tgmx = new \app\admin\model\Tgmx();
        $map['status'] = 0;
        $buylist = $Tgmx->where($map)->order('number DESC')->limit($from, $to)->select();
        if ($buylist) {
            foreach ($buylist as $key => $item) {
                $buylist[$key]['addtime'] = date('m-d H:s', $item['addtime']);
                $buylist[$key]['num'] = $item['number'] - $item['buy_number'];
            }
        }
        $data['buy'] = $buylist;
        return json($data);
    }

    /**
     * 历史成交
     * @param number $from 开始
     * @param number $to 结束
     */
    public function history()
    {
        $from = input('from', 0);
        $to = input('to', 1);
        $Ppmx = new \app\common\model\Ppmx();
        $myset = config('site');
        $map['userid|userid1'] = session('uid');
        $map['status'] = ['in', [0, 1, 3, 4]];
        $tradeorder = $Ppmx->where($map)->order('ppid DESC')->limit($from, $to)->select();

        if ($tradeorder) {
            foreach ($tradeorder as $key5 => $item5) {
                $end1 = $item5['addtime'] + $myset['zdqx'] * 3600;
                $end2 = $item5['paytime'] + $myset['zdqr'] * 3600;
                //卖家
                $tmp = [0 => '<a style="color: red;" href="' . url('index/business/dkdetail') . '?ppid=' . $item5['ppid'] . '" >买家待付款</a>',
                        1 => '<a style="color: green;" href="' . url('index/business/dkdetail') . '?ppid=' . $item5['ppid'] . '" >买家已付款待确认</a>',
                        2 => '已完成',
                        3 => '已取消',
                        4 => '投诉'];
                //买入
                $tmp2 = [0 => '<a style="color: red;" href="' . url('index/business/skdetail') . '?ppid=' . $item5['ppid'] . '" >点击付款</a>',
                         1 => '<a style="color: green;" href="' . url('index/business/skdetail') . '?ppid=' . $item5['ppid'] . '" >已付款待对方确认</a>',
                         2 => '已完成',
                         3 => '已取消',
                         4 => '投诉'];
                //买入
                $tmp3 = [0 => '<span >剩余打款时间:</span><span style="font-size: smaller;" class="untime" data-time="' . $end1 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         1 => '<span >剩余确认时间:</span><span style="font-size: smaller;" class="toutime" data-time="' . $end2 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         2 => '',
                         3 => '',
                         4 => ''];
                //卖出
                $tmp4 = [0 => '<span >剩余打款时间:</span><span style="font-size: smaller;" class="toutime" data-time="' . $end1 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         1 => '<span >剩余确认时间:</span><span style="font-size: smaller;" class="untime" data-time="' . $end2 . '" data-ppid="' . $item5['ppid'] . '"></span>',
                         2 => '',
                         3 => '',
                         4 => ''];
                if ($item5['userid'] == session('uid')) {
                    $tradeorder[$key5]['type'] = '买入';
                    $tradeorder[$key5]['status'] = $tmp2[$item5['status']];
                    $tradeorder[$key5]['endtime'] = $tmp3[$item5['status']];
                } else {
                    $tradeorder[$key5]['type'] = '卖出';
                    $tradeorder[$key5]['status'] = $tmp[$item5['status']];
                    $tradeorder[$key5]['endtime'] = $tmp4[$item5['status']];
                }
                $tradeorder[$key5]['addtime'] = date('m-d H:s', $item5['addtime']);

            }
        }

        $data['tradeorder'] = $tradeorder;  //个人交易
        return json($data);
    }

    /**
     * 虚拟币认购
     */
    public function regou()
    {
        $myset = config('site');

        $data = input('post.');
        $wall3 = $data['wall3'];
        $wall6 = $data['wall6'];
        $wall6 = $wall6 == '' ? 0 : $wall6;
        $wall3 = $wall3 == '' ? 0 : $wall3;
        if ($wall3 < 0 || $wall6 < 0) {
            $this->error('请输入正确金额');
        }
        $total = $wall3 + $wall6;
        $rgstart = strtotime($myset['rgstart']);  //认购开始时间
        $rgend = strtotime($myset['rgend']);  //认购结束时间
        $fxprice = $myset['fxprice'];  //发行金额
        $info = Db::table('fa_config')->where('name', 'fxnum')->lock(true)->find();

        if (time() < $rgstart || time() > $rgend) {
            $this->error('认购开放时间为' . $myset['rgstart'] . '--' . $myset['rgend']);
        }
        if ((int)$info['value'] == 0) {
            $this->error('认购数量已完');
        }
        $rshy = $this->auth->getUserinfo();
        if ($total > $rshy['wall2'] / $fxprice) {
            $this->error('认购额度不足');
        }
        if ($rshy['wall3'] / $fxprice < $wall3) {
            $this->error('您的' . $myset['walltype']['wall3'] . '余额不足！');
        }
        if ($rshy['wall6'] / $fxprice < $wall6) {
            $this->error('您的' . $myset['walltype']['wall6'] . '余额不足！');
        }
        if ($wall3 > 0) {
            caiwu($rshy['id'], -$wall3 * $fxprice, 11, 'wall3', '认购虚拟币');   //扣除钱包
        }
        if ($wall6 > 0) {
            caiwu($rshy['id'], -$wall6 * $fxprice, 11, 'wall6', '认购虚拟币');   //扣除钱包
        }
        caiwu($rshy['id'], $total, 11, 'wall1', '认购虚拟币');    //增加虚拟币
        caiwu($rshy['id'], -$total * $fxprice, 11, 'wall2', '认购虚拟币');    //扣除额度
        $fx = (int)$info['value'] - $total;
        $re = Db::table('fa_config')->where('name', 'fxnum')->setField('value', $fx);
        if ($re) {
            $this->success('认购成功', '', url('index/user/index'));
        } else {
            $this->error('认购失败', '', url('index/user/index'));
        }

//        }
    }

    public function pay()
    {
        $data = $this->request->param();
        $rshy = $this->auth->getUserinfo();
        $myset=config('site');

//        $count = Db::name('caiwu')->where(['userid' => $rshy['id'], 'type' => 11])->whereTime('addtime', 'today')->count();
//        if ($count > 0) {
//            $this->error('今日已认购', 789);
//        }
//        var_dump($rshy);exit;
        //session('payinfo',$data);
        if ($data['wall3'] < 0 || !$data['wall3']) {
            $this->error('数量有误');
        }
        $price = $myset['fxprice'] * $data['wall3'];
        $data['price'] = $price;
        $data['bankcode']= 'kjpay';

        $map['price'] = $price;
        $map['user_id'] = $rshy['user_id'];
        $map['nickname'] = $rshy['username'];
        $map['order_sn'] = 'recharge' . get_rand_str(10, 0, 1);
        $map['ctime'] = time();
        $map['pay_code'] = $data['paytype'];
        $map['pay_name'] = $data['bankcode'];
        $map['pay_num'] = $data['wall3'];
        $order_id = Db::name('recharge')->insert($map);

        if ($order_id) {
            $data['sdorderno'] = $map['order_sn'];
            $this->success('', 0, url('index/trade/pay', $data));
        } else {
            $this->error('购买失败');
        }

    }

    /**
     * 充币
     */
    public function etc2lmc()
    {
        $user = $this->auth->getUserinfo();
        $data = $this->request->param();
        $walltype = config('site.walltype');
        $myset = config('site');
        if (time() < strtotime($myset['tgopen']) || time() >= strtotime($myset['tgclose'])) {
            $this->error("充币开放时间{$myset['tgopen']}--{$myset['tgclose']}");
        }
        if (!$data['type']) {
            $this->error('币种不能为空');
        }
        if ($data['number'] < 0 || !$data['number']) {
            $this->error('数量有误');
        }
        $service = config('site')['chargefee'] * $data['number'] * 0.01;

        if (!Sms::check($data['phone'], $data['captcha'], 'tb')) {
            $this->error(__('验证码不正确'));
        }
        $urls = controller('common')->upload2();
        $map = [
            'user_id'    => $user['id'],
            'account'    => $user['username'],
            'amount'     => $data['number'] - $service,
            'addtime'    => time(),
            'service'    => $service,//手续费
            'paytype'    => $walltype[$data['type']],
            'type'       => $data['type']
        ];
        $all = array_merge($map, $urls);
        //增加
        $order_id = Db::name('tx_eth2lmc')->insertGetId($all);
        if ($order_id) {
            $this->success('提交成功', 789);
        } else {
            $this->error('提交失败');
        }

    }

    /**
     * 提币
     */
    public function lmc2etc()
    {
        $user = $this->auth->getUserinfo();
        $data = $this->request->param();
        $walltype = config('site.walltype');
//    ["type"] => string(5) "wall3"
//    ["number"] => string(8) "328.2000"
//    ["userwallet"] => string(0) ""
//    ["phone"] => string(11) "19937689493"
//    ["captcha"] => string(0) ""
        $myset = config('site');
        if (time() < strtotime($myset['xyopen']) || time() >= strtotime($myset['xyclose'])) {
            $this->error("提币开放时间{$myset['xyopen']}--{$myset['xyclose']}");
        }
        if (!$data['type']) {
            $this->error('币种不能为空');
        }
        if (!$data['userwallet']) {
            $this->error('提现钱包地址不能为空');
        }
//        if ($data['type'] =='wall'){
//            $this->error('对接中');
//        }
        if ($data['number'] < 0 || !$data['number']) {
            $this->error('数量有误');
        }
        $service = config('site')['service'] * $data['number'] * 0.01;
        if ($data['number'] % 10 != 0) {
            $this->error("提币数量超高");
        }
        if ($user['slrate'] < 10) {
            $this->error("算力不足");
        }
        if ($data['number'] + $service > $user[$data['type']]) {
            $this->error('额度不足' . $user[$data['type']]);
        }
        if (!Sms::check($data['phone'], $data['captcha'], 'tb')) {
            $this->error(__('验证码不正确'));
        }

        //增加
        $order_id = Db::name('tx_lmc2eth')->insertGetId([
            'user_id'    => $user['id'],
            'account'    => $user['username'],
            'amount'     => $data['number'] - $service,
            'addtime'    => time(),
            'userwallet' => $data['userwallet'],
            'service'    => $service,//手续费
            'paytype'    => $walltype[$data['type']],
            'type'       => $data['type']
        ]);

        if ($order_id) {
            caiwu($user['id'], -$data['number'], 13, $data['type'], '提现申请',1);
            $this->success('提交成功', 789);
        } else {
            $this->error('提交失败');
        }

    }
//    public function withdraw(){
//        $type = input('type');
//        if (!$type){
//            $this->error('对接中');
//        }
//    }
    public function bbsd()
    {
        $user = $this->auth->getUserinfo();
        $data = $this->request->param();
        $wall = config('site.walltype');
        $myset = config('site');

        switch ($data['ptype']) {
            case 'p1':
                if (time() < strtotime($myset['btc2lmcopen']) || time() >= strtotime($myset['btc2lmcclose'])) {
                    $this->error("兑换开放时间{$myset['btc2lmcopen']}--{$myset['btc2lmcclose']}");
                }
                if (!$data['phone']) {
                    $this->error('请输入手机号');
                }
                if (!$data['captcha']) {
                    $this->error('请输入短信验证码');
                }
                if (!Sms::check($data['phone'], $data['captcha'], 'bbsd')) {
                    $this->error(__('验证码不正确'));
                }
                if ($data['number'] < 0 || !$data['number']) {
                    $this->error('数量有误');
                }
                if ($data['number'] > $user[$data['type']]) {
                    $this->error('额度不足');
                }
                if ($user['slrate'] < 10) {
                    $this->error("算力不足");
                }
                $num = $data['number'] * $data['hl']*(1-$myset['b2c'] * 0.01);//lmc

                caiwu($user['id'], -$data['number'], 31, $data['type'], $wall[$data['type']] . '兑出');
                caiwu($user['id'], $num, 31, 'wall2', $wall['wall2'].'兑入');
                break;
            case 'p2':
                if (time() < strtotime($myset['lmc2open']) || time() >= strtotime($myset['lmc2close'])) {
                    $this->error("兑换开放时间{$myset['lmc2open']}--{$myset['lmc2close']}");
                }
                if (!$data['phone']) {
                    $this->error('请输入手机号');
                }
                if (!$data['captcha']) {
                    $this->error('请输入短信验证码');
                }
                if (!Sms::check($data['phone'], $data['captcha'], 'bbsd')) {
                    $this->error(__('验证码不正确'));
                }
                if ($data['number'] < 0 || !$data['number']) {
                    $this->error('数量有误');
                }
                if ($user['wall7'] < $data['number']) {
                    $this->error("授信额度不足");
                }
                if ($user['slrate'] < 100) {
                    $this->error("算力不足");
                }
                if ($data['number'] > $user['wall2']) {
                    $this->error('LMC数量不足');
                }
                $num = $data['number']*(1-$myset['c2b'] * 0.01) / $data['hl'];
                caiwu($user['id'], -$data['number'], 31, 'wall2', $wall['wall2'].'兑出');
                caiwu($user['id'], -$data['number'], 31, 'wall7', $wall['wall2'].'兑出');
                caiwu($user['id'], $num, 31, $data['type'], $wall[$data['type']] . '兑入');
                break;
        }

        $this->success('提交成功', 789);
    }

}
