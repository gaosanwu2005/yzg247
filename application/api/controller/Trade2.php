<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;

/**
 * 互助交易接口
 */
class Trade2 extends Api
{

    protected $noNeedLogin = ['index'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
//        $this->model = model('Ppmx2');

    }

    //公共检查
    private function comcheck($btc, $cny, $type = 'buy')
    {
        $password = input('post.password2');
        $myset = config('site');
        $rshy = $this->auth->getUserinfo();
        if (!$cny) $this->error('请输入价格');
        if ($btc <= 0) {
            $this->error('请输入正确的金额');
        }
        if ($rshy['jointime'] == 0) {
            $this->error('请激活账号再操作！', '', '');
        }
        if ($rshy['realname'] == '' || $rshy['alipay'] == '') {
            $this->error('请先进行身份认证！', '', url('index/Info/editinfo'));
        }
//        if (date('H') < $myset['opentime'] || date('H') >= $myset['closetime']) {
//            $this->error("开放时间{$myset['opentime']}点--{$myset['closetime']}点");
//        }
//        if ($btc < $myset['ntbmin'] || $btc > $myset['ntbmax']) {
//            $this->error("交易额度必须在{$myset['ntbmin']} -{$myset['ntbmax']}以内！");
//        }
//        if (authcode($rshy['password2'], 'DECODE') != $password) {
//            $this->error('您输入的交易密码错误');
//        }
//        if ($type == 'buy') {
//            $Tgmx2 = new \app\admin\model\Tgmx2();
//            $lastg = $Tgmx2->where(['userid' => $rshy['id'], 'status' => ['in', [0, 5]]])->order('addtime desc')->find();
//            if ($btc < $lastg['number']) {
//                $this->error('复投金额不能低于上轮投资额度');
//            }
//            if ($lastg && (time() - $lastg['addtime']) < $myset['pdspace'] * 86400) {
//                $this->error("两次交易的间隔不能小于{$myset['pdspace']}天");
//            }
//        }
    }

    //指定匹配
    public function clicksale()
    {
        $tgid = input('id', 0);
        if (!$tgid) {
            $this->error('订单不存在');
        }
        $Tgmx2 = new \app\admin\model\Tgmx2();
        $Xymx2 = new \app\admin\model\Xymx2();
        $Ppmx2 = new \app\common\model\Ppmx2();
        $user = $this->auth->getUserinfo();
        $buy = $Tgmx2->where(['tgid' => $tgid, 'status' => 0])->find();
        if (!$buy) {
            $this->error('订单不存在');
        }
        if ($buy['userid'] == $user['id']) {
            $this->error('不能跟自己交易');
        }
        $number = $buy['number'] - $buy['buy_number'];
        $this->comcheck($number, 1, 'buy');
        $fee=0;
        //增加卖出记录
        $Ppmx2->sale($number, 1, $fee,$user,'wall2');
        $saleid = $Ppmx2->btc_sale_id;
        $sale = $Xymx2->where(['xyid' => $saleid, 'status' => '0'])->find();
        //匹配金额记录
        $dd = $Ppmx2->trade($number, $sale, $buy, 5);
        if ($dd) {
//            smscode(session('uid'),1,session('username'));
//            smscode($buy['userid'],1,$buy['account']);
            $this->success('提交成功','321');
        } else {
            $this->error('提交失败，请稍后再试');
        }
    }

    //指定匹配
    public function clickbuy()
    {
        $xyid = input('id', 0);
        if (!$xyid) {
            $this->error('订单不存在');
        }
        $Tgmx2 = new \app\admin\model\Tgmx2();
        $Xymx2 = new \app\admin\model\Xymx2();
        $Ppmx2 = new \app\common\model\Ppmx2();
        $user = $this->auth->getUserinfo();
        $sale = $Xymx2->where(['xyid' => $xyid, 'status' => 0])->find();
        if (!$sale) {
            $this->error('订单不存在');
        }
        if ($sale['userid'] ==  $user['id']) {
            $this->error('不能跟自己交易');
        }
        $number = $sale['number'] - $sale['sale_number'];
        $this->comcheck($number, 1,'sale');
        $fee=0;
        //增加购买记录
        $Ppmx2->buy($number, 1, $fee, $user);
        $buyid = $Ppmx2->btc_buy_id;
        $buy = $Tgmx2->where(['tgid' => $buyid, 'status' => 0])->find();
        //增加匹配记录
        $dd = $Ppmx2->trade($number, $sale, $buy, 4);
        if ($dd) {
//            smscode(session('uid'),1,session('username'));
//            smscode($sale['userid'],1,$sale['account']);
            $this->success('提交成功','321');
        }else {
            $this->error('提交失败，请稍后再试');
        }

    }


    /**
     * 互助买单
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function buy()
    {
        $user = $this->auth->getUserinfo();
        $myset = config('site');
        $btc = input('btc', 0);     //数量id
        $touzi = db('Tzrank')->where('rid', $btc)->find();
        $btc = $touzi['rprice'];     //排单金额
        $cny =  $touzi['flrate'];     //返利比率
        $this->comcheck($btc, $cny, 'buy');
        if (time() < strtotime($myset['tgopen']) || time() >= strtotime($myset['tgclose'])) {
            $this->error("排单开放时间{$myset['tgopen']}--{$myset['tgclose']}");
        }
        $fee = 0;    //排单币
//        if ($user['wall7'] < $touzi['pdfee']) {
//            $this->error('排单币不足');
//        }
        $Ppmx2 = new \app\common\model\Ppmx2();
        if ($Ppmx2->buy($btc, $cny, $fee, $user)) {
            // 自动交易
//            if ($Ppmx2->findSale($Ppmx2->btc_buy_id)) {
//                $data['info'] = '交易中……，请稍后到已完成查看';
//            }
//            $this->success('提交成功,请到交易页面查看', ['fresh' => 1], url('index/user/index'));  //刷新
            $this->success('提交成功','321');
        } else {
            $this->error($Ppmx2->getError());
        }
    }

    /**
     *
     */
    public function sale()
    {
        $myset = config('site');
        $user = $this->auth->getUserinfo();
        $btc = input('btc', 0);     //数量id
        $cny = input('param.cny', 1);     //单价
        $touzi = db('Tzrank')->where('rid', $btc)->find();
        $btc = $touzi['rprice'];     //排单金额
        $this->comcheck($btc, $cny, 'sale');
        if ($user['wall2'] < $btc) {
            $this->error('您的' . $myset['walltype']['wall2'] . '余额不足！');
        }
        $fee = 0;
        $Ppmx2 = new \app\common\model\Ppmx2();
        if ($Ppmx2->sale($btc, $cny, $fee, $user,'wall2')) {
            // 自动交易
//            if ($Ppmx2->findbuy($Ppmx2->btc_sale_id)) {
//                $data['info'] = '交易中……，请稍后到已完成查看';
//            }
//            $this->success('提交成功,请到交易页面查看', ['fresh' => 1], url('index/user/index'));    //刷新
            $this->success('提交成功','321');
        } else {
            $this->error($Ppmx2->getError());
        }
    }

    public function gosale()
    {
        $myset = config('site');
        $data = input('post.');
        $wall1 = $data['wall1'];
        $fee = 0;
        $wall1 = $wall1 == '' ? 0 : $wall1;
        if ($wall1 < 0  ) {
            $this->error('请输入正确金额');
        }
        $total = $wall1;
        $this->comcheck($total, 1, 'sale');
        if (time() < strtotime($myset['xyopen']) || time() >= strtotime($myset['xyclose'])) {
            $this->error("提现开放时间{$myset['xyopen']}--{$myset['xyclose']}");
        }
        $rshy = $this->auth->getUserinfo();

        if ($rshy['wall1'] < $wall1) {
            $this->error('您的' . $myset['walltype']['wall1'] . '余额不足！');
        }

        if ($wall1 > 0) {
            if ($wall1 < $myset['hztx']['min'] || $wall1 > $myset['hztx']['max']) {
                $this->error('提现金额需要在' . $myset['hztx']['min'] . '--' . $myset['hztx']['max'] . '范围内');
            }
            if ($wall1 % $myset['hztx']['bei'] != 0) {
                $this->error('提现金额必须是' . $myset['hztx']['bei'] . '的倍数');
            }
//            if ($rshy['txwall1'] >= $myset['hztx']['day']) {
//                $this->error($myset['walltype']['wall1'] . '每天只能提现' . $myset['hztx']['day'] . '次');
//            }

        }
        $Ppmx2 = new \app\common\model\Ppmx2();

        if ($wall1 > 0) {
            $r = $Ppmx2->sale($wall1, 1, $fee, $rshy, 'wall1');
        }
        if ($r) {
            $this->success('提交成功', ['fresh' => 1], url('index/user/index'));    //刷新
        } else {
            $this->error($Ppmx2->getError());
        }

    }

    /**
     * 撤销卖单
     * @param string $sale 卖单编号
     * @return \think\response\Json
     */
    public
    function CancelSaleOrder()
    {

        $id = input('sale');
        $Ppmx2 = new \app\common\model\Ppmx2();
        if ($Ppmx2->cancelSaleOrder(session('uid'), $id)) {
            $this->success('撤单成功');
        } else {
            $this->error($Ppmx2->getError());
        }
    }

    /**
     * 撤销买单
     * @param string $buy 买单编号
     * @return \think\response\Json
     */
    public
    function cancelBuyOrder()
    {

        $id = input('buy');
        $Ppmx2 = new \app\common\model\Ppmx2();
        if ($Ppmx2->cancelBuyOrder(session('uid'), $id)) {
            $this->success('撤单成功');
        } else {
            $this->error($Ppmx2->getError());
        }


    }

    /**
     * 列表行情
     */
    public
    function hangqing()
    {
        $user = $this->auth->getUserinfo();
        $Tgmx2 = Db::name('Tgmx2');
        $Xymx2 = Db::name('Xymx2');
        $Ppmx2 = Db::name('Ppmx2');
        $myset = config('site');
//        $buylist = $Tgmx2->where('status',0)->order('price DESC')->limit(10)->select();
//        $salelist = $Xymx2->where('status',0)->order('price ASC')->limit(10)->select();
        $buytrade = [];
        $saletrade = [];
        $map['userid'] = $user['id'];
        $map['status'] = 0;
        $map2['userid|userid1'] = $user['id'];
        $map2['status'] = ['in', [0, 1, 4]];
        $buyorder = $Tgmx2->where($map)->order('tgid DESC')->limit(20)->select();
        $saleorder = $Xymx2->where($map)->order('xyid DESC')->limit(20)->select();
        $tradeorder = $Ppmx2->where($map2)->order('ppid DESC')->limit(10)->select();

//        if ($buylist) {
//            foreach ($buylist as $key => $item) {
//                $buylist[$key]['addtime'] = date('Y-m-d H:i', $item['addtime']);
//                $buylist[$key]['num'] = $item['number'] - $item['buy_number'];
//                $buylist[$key]['total'] = round($buylist[$key]['num'] * $item['price'],2);
//            }
//        }
//        if ($salelist) {
//            foreach ($salelist as $key2 => $item2) {
//                $salelist[$key2]['addtime'] = date('Y-m-d H:i', $item2['addtime']);
//                $salelist[$key2]['num'] = $item2['number'] - $item2['sale_number'];
//                $salelist[$key2]['total'] = round($salelist[$key2]['num'] * $item2['price'],2);
//            }
//        }
        if ($buyorder) {
            foreach ($buyorder as $key3 => $item3) {
                $buyorder[$key3]['pai'] = (int)((time() - $item3['addtime']) / 86400);
                $buyorder[$key3]['addtime'] = date('Y-m-d H:i', $item3['addtime']);
                $buyorder[$key3]['realname'] = $user['realname'];
            }
        }
        if ($saleorder) {
            foreach ($saleorder as $key4 => $item4) {
                $saleorder[$key4]['pai'] = (int)((time() - $item4['addtime']) / 86400);
                $saleorder[$key4]['addtime'] = date('Y-m-d H:i', $item4['addtime']);
                $saleorder[$key4]['realname'] = $user['realname'];
            }
        }
        if ($tradeorder) {
            foreach ($tradeorder as $key5 => $item5) {
                $end1 = $item5['addtime'] + $myset['zdqx'] * 3600;
                $end2 = $item5['paytime'] + $myset['zdqr'] * 3600;
                //卖家
                $tmp = [0 => '<a style="color: red;" href="' . url('/index/business2/dkdetail') . '?ppid=' . $item5['ppid'] . '" >买家待付款</a>',
                        1 => '<a  class="button button-round active" href="' . url('/index/business2/dkdetail') . '?ppid=' . $item5['ppid'] . '" >买家已付款待确认</a>',
                        2 => '已完成',
                        3 => '已取消',
                        4 => '投诉中'];
                //买入
                $tmp2 = [0 => '<a  class="button button-round active" href="/index/business2/skdetail?ppid=' . $item5['ppid'] . '" >点击付款</a>',
                         1 => '<a style="color: green;" href="' . url('/index/business2/skdetail') . '?ppid=' . $item5['ppid'] . '" >已付款待对方确认</a>',
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
//                    $tradeorder[$key5]['type'] = '买入';
//                    $tradeorder[$key5]['status'] = $tmp2[$item5['status']];
//                    $tradeorder[$key5]['endtime'] = $tmp3[$item5['status']];
                    $buytrade[] = [
                        'status'   => $tmp2[$item5['status']],
                        'endtime'  => $tmp3[$item5['status']],
                        'addtime'  => date('m-d H:i', $item5['addtime']),      //匹配时间
                        //                        'tgtime'=> date('m-d H:i', $item5['tgtime']),        //排单时间
                        'number'   => $item5['number'],
                        'username' => $item5['account1'],
                    ];

                } else {
//                    $tradeorder[$key5]['type'] = '卖出';
//                    $tradeorder[$key5]['status'] = $tmp[$item5['status']];
//                    $tradeorder[$key5]['endtime'] = $tmp4[$item5['status']];
                    $saletrade[] = [
                        'status'   => $tmp[$item5['status']],
                        'endtime'  => $tmp4[$item5['status']],
                        'addtime'  => date('m-d H:i', $item5['addtime']),      //匹配时间
                        //                        'xytime'=> date('m-d H:i', $item5['xytime']),         //排单时间
                        'number'   => $item5['number'],
                        'username' => $item5['account'],
                    ];
                }
//                $tradeorder[$key5]['addtime'] = date('m-d H:i', $item5['addtime']);

            }
        }
        $data['buyorder'] = $buyorder;  //个人买
        $data['saleorder'] = $saleorder;  //个人卖
//        $data['tradeorder'] = $tradeorder;  //个人交易
        $data['buytrade'] = $buytrade ? $buytrade : 0;  //个人买 交易
        $data['saletrade'] = $saletrade ? $saletrade : 0;  //个人卖 交易
//        $data['buyorder'] = $buyorder;  //个人买
//        $data['saleorder'] = $saleorder;  //个人卖
//        $data['tradeorder'] = $tradeorder;  //个人交易

//        $data['buy'] = $buylist;
//        $data['sale'] = $salelist;
//        $data['tradelist'] = $tradelist;
        return json($data);
    }


    /**
     * 获取更多卖单
     * @param number $from 开始
     * @param number $to 结束
     */
    public
    function saleajax()
    {
        $from = input('from', 0);
        $to = input('to', 1);
        $Xymx2 = new \app\admin\model\Xymx2();
        $map['status'] = 0;
        $salelist = $Xymx2->where($map)->order('number ASC')->limit($from, $to)->select();
        if ($salelist) {
            foreach ($salelist as $key2 => $item2) {
                $salelist[$key2]['addtime'] = date('m-d H:i', $item2['addtime']);
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
    public
    function buyajax()
    {
        $from = input('from', 0);
        $to = input('to', 1);
        $Tgmx2 = new \app\admin\model\Tgmx2();
        $map['status'] = 0;
        $buylist = $Tgmx2->where($map)->order('number DESC')->limit($from, $to)->select();
        if ($buylist) {
            foreach ($buylist as $key => $item) {
                $buylist[$key]['addtime'] = date('m-d H:i', $item['addtime']);
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
    public
    function history()
    {
        $from = input('from', 0);
        $to = input('to', 1);
        $Ppmx2 = new \app\common\model\Ppmx2();
        $myset = config('site');
        $map['userid|userid1'] = session('uid');
        $map['status'] = ['in', [0, 1, 3, 4]];
        $tradeorder = $Ppmx2->where($map)->order('ppid DESC')->limit($from, $to)->select();

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
                $tradeorder[$key5]['addtime'] = date('m-d H:i', $item5['addtime']);

            }
        }

        $data['tradeorder'] = $tradeorder;  //个人交易
        return json($data);
    }

    /**
     * 提现到互助钱包
     */
    public
    function profit()
    {
        $id = input('id');
        $myset = config('site');
        if ($id > 0) {
            //开启事务,避免出现垃圾数据
            Db::startTrans();
            try {
                $info = db('profit')->where('id', $id)->find();
                if (time() - ($info['add_time'] + 86400 * $myset['pdspace']) > 0) {
                    if (caiwu($info['user_id'], $info['money'], 7, 'wall3', '提现到互助钱包')) {
                        caiwu($info['user_id'], $info['profit'], 7, 'wall6', '互助利息');
                        caiwu($info['user_id'], $info['profit'], 7, 'wall2', '互助利息动态额度');
                        db('profit')->where('id', $id)->delete();
                        Db::commit();
                        $this->success('提现成功', '', url('index/business/index'));
                    }
                } else {
                    $this->error('提现失败，时间未到');
                }

            } catch (DbException $e) {
                $this->error($e->getMessage());
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

        }
    }

}
