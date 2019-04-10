<?php

namespace app\common\model;

use think\Db;
use think\Exception;
use think\Log;
use think\Model;

class Ppmx2 extends Model
{
    public $btc_sale_id = 0, $btc_buy_id = 0;
    protected $_error = '';
    // 表名
    protected $name = 'ppmx2';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'confirmtime_text',
        'type_text',
        'status_text'
    ];

    public function getTypeList()
    {
        return ['4' => '买入', '5' => '卖出'];
    }

    public function getStatusList()
    {
        return ['0' => '待打款', '1' => '已付款', '2' => '已完成', '3' => '已取消', '4' => '投诉'];
    }


    public function getConfirmtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['confirmtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setConfirmtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    /**
     * 互助卖出
     * @param $btc
     * @param $cny
     * @param $fee
     * @return bool
     */
    function sale($btc, $cny, $fee,$user=array(),$paytype)
    {

        $btc = floatval($btc);
        $cny = floatval($cny);
        $fee = floatval($fee);
        if(!isset($user['id'])){
            $this->setError('用户不存在');
            return false;
        }
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            //减少余额
            caiwu($user['id'], -$btc, 3, $paytype, '卖出');
            //扣除手续费
            if ($fee > 0) caiwu($user['id'], -$fee, 4, $paytype, "卖出手续费");
            //冻结
//            Db::name('user')->where('id=' . $user['id'])->setInc('wall5', $btc);

            //提现次数
            Db::name('user')->where('id=' . $user['id'])->setInc('tx'.$paytype, 1);
            //增加销售记录
            $row = array(
                'userid'  => $user['id'],
                'account' => $user['username'],
                'number'  => $btc,
                'price'   => $cny,
                'fee'     => $fee,
                'total'   => $cny * $btc,
                'ptype'   => $paytype,
                'status'  => 0,
                'addtime' => time(),
                'salesn'   => 'S'.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8),

            );
            $this->btc_sale_id = Db::name('xymx2')->insertGetId($row);
            Db::commit();
            return true;

        } catch (Exception $e) {
            $this->setError($e->getMessage());
            Db::rollback();
            return false;
        }
    }

    /**
     * 互助买
     * @param $btc
     * @param $cny
     * @param $fee
     * @return bool
     */
    function buy($btc, $cny, $fee,$user=array())
    {


        $btc = floatval($btc);
        $cny = floatval($cny);
        $fee = floatval($fee);
        if(!isset($user['id'])){
            $this->setError('用户不存在');
            return false;
        }

        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            //减少余额
//            caiwu($user['id'], -$btc * $cny, 2, 'wall2', '买入');
            //扣除排单币
            if ($fee > 0) caiwu($user['id'], -$fee, 4, 'wall7', "排单消费");
            //增加购买记录
            $row = array(
                'userid'  => $user['id'],
                'account' => $user['username'],
                'number'  => $btc,
                'flrate'   => $cny,
                'fee'     => $fee,
                'total'   => $cny * $btc,
                'status'  => 0,
                'addtime' => time(),
                'buysn'   => 'B'.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8),

            );

            $this->btc_buy_id = Db::name('tgmx2')->insertGetId($row);
            Db::commit();
            return true;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            Db::rollback();
            return false;
        }
    }


    //撤销卖单
    function cancelSaleOrder($uid, $id)
    {
        $myset = config('site');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $Xymx =Db::table('fa_xymx2');
            $one = $Xymx->find($id);
            if ($one['userid'] != $uid) {
                return false;
            }
            if ($one['status'] == 3) {
                return false;
            }
            if ($one['sale_number'] == $one['number']) {
                return false;
            }
            $data['status'] = 3;
            $Xymx->where(array("xyid" => $id))->setField($data);
            //剩余 btc
            $left = $one['number'] - $one['sale_number'];
            if ($left < 0) {
                return false;
            }

            $fee = ceil($left * $myset['salefee'] * 0.01);
            caiwu($uid, $left, 6, 'wall1', '撤销卖单');
            if($fee>0) caiwu($uid, $fee, 6, 'wall1', '撤销卖单手续费退回');
            //解冻
//            Db::name('user')->where('id=' . $uid)->setDec('wall5', $left);
            Db::commit();
            return true;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            Db::rollback();
            return false;
        }
    }

    //撤销买单
    function cancelBuyOrder($uid, $id)
    {
//        $myset = config('site');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $Tgmx =Db::table('fa_tgmx2');
            $one = $Tgmx->find($id);
            if ($one['userid'] != $uid) {
                return false;
            }
            if ($one['status'] == 3) {
                return false;
            }
            if ($one['buy_number'] == $one['number']) {
                return false;
            }
            $data['status'] = 3;
            $Tgmx->where(array("tgid" => $id))->setField($data);
            //剩余
            $left = $one['number'] - $one['buy_number'];
            if ($left < 0) {
                return false;
            }
            //记录
//            $fee = round($left * $one['price'] * $myset['buyfee'] * 0.01, 2);
//            caiwu($uid, $left * $one['price'], 6, 'wall2', '撤销买单');
//            caiwu($uid, $fee, 6, 'wall2', '撤销买单手续费退回');
            Db::commit();
            return true;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            Db::rollback();
            return false;
        }
    }


    //找到卖价交易 挂单 buy的时候执行

    /**
     * @param $btc_buy_id
     * @return bool
     */
    function findSale($btc_buy_id)
    {
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $Tgmx =Db::table('fa_tgmx2');
            $Xymx =Db::table('fa_xymx2');
            $buy = $Tgmx->find($btc_buy_id);
            //找到卖价不高于买家最低的
            $map['status'] = 0;
            $map['userid'] = ['neq', $buy['userid']];
            $map['price'] = array('elt', $buy['price']);
            $sale = $Xymx->where($map)->find();
            if (!$sale) {
                return false;
            }
            //还要买的量  
            $leftBuy = $buy['number'] - $buy['buy_number'];
            //还要卖的量
            $leftSale = $sale['number'] - $sale['sale_number'];
            //交易数量
            $number = min($leftSale, $leftBuy);
            if (!$number) {
                return false;
            }
//            \Think\Log::record('-------------'.$leftBuy );
//            \Think\Log::record('-------------'.$number );
            $this->trade($number, $sale, $buy, 4);
            if ($leftBuy - $number > 0) {
                $this->findSale($btc_buy_id);
            }
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    //找到买家 挂单sale时候执行
    function findBuy($btc_sale_id)
    {
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $Tgmx =Db::table('fa_tgmx2');
            $Xymx =Db::table('fa_xymx2');
            $sale = $Xymx->find($btc_sale_id);
            //找到买价高于卖价最高的
            $map['status'] = 0;
            $map['userid'] = ['neq', $sale['userid']];
            $map['price'] = array('egt', $sale['price']);
            $buy = $Tgmx->where($map)->find();
            if (!$buy) {
                return false;
            }
            //还要买的量  
            $leftBuy = $buy['number'] - $buy['buy_number'];
            //还要卖的量
            $leftSale = $sale['number'] - $sale['sale_number'];
            //交易数量
            $number = min($leftSale, $leftBuy);
            if (!$number) {
                return false;
            }
            $this->trade($number, $sale, $buy, 5);
            if ($leftSale - $number > 0) {
                $this->findBuy($btc_sale_id);
            }
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 交易
     * @param $number
     * @param $sale
     * @param $buy
     * @param $type
     * @return bool
     * @throws \think\exception\PDOException
     */
    function trade($number, $sale, $buy, $type)
    {
//开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $sale_id = (int)$sale['userid'];
            $buy_id = (int)$buy['userid'];
            Db::table('fa_xymx2')->where(array('xyid' => $sale['xyid']))->setInc('sale_number', $number);
            Db::table('fa_tgmx2')->where(array('tgid' => $buy['tgid']))->setInc('buy_number', $number);
            //记录
            $data['status'] = 0;   //待打款
            $data['tgid'] = $buy['tgid'];
            $data['xyid'] = $sale['xyid'];
            $data['account'] = $buy['account'];
            $data['account1'] = $sale['account'];
            $data['userid'] = $buy_id;
            $data['userid1'] = $sale_id;
            $data['type'] = $type;
            $data['price'] = $buy['price'];
            $data['number'] = $number;
            $data['total'] = $number * $buy['price'];
            $data['addtime'] = time();
            $data['ppsn'] =  'P'.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            Db::table('fa_ppmx2')->insert($data);
            //短信通知
//            smscode($buy_id, 1, $buy['account']);
//            smscode($sale_id, 1, $sale['account']);
            //判断买卖状态
            if ($sale['number'] == $number + $sale['sale_number']) {
                Db::table('fa_xymx2')->update(['status' => '5','comtime' => time(),'xyid'=> $sale['xyid']]);
            }

            if ($buy['number'] == $number + $buy['buy_number']) {
                Db::table('fa_tgmx2')->update(['status' => '5','comtime' => time(),'tgid'=> $buy['tgid']]);
            }
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
        //echo date('Y-m-d H:i:s')." sale $sale_id buy $buy_id";
    }


    /**
     *  手动匹配
     * @param $btc_buy_id
     * @return bool
     */
    function findSale2($buy_id, $sale_id, $number)
    {
        Db::startTrans();
        try { 
            $buy = Db::table('fa_tgmx2')->find($buy_id);
            $sale = Db::table('fa_xymx2')->find($sale_id);
            //还要买的量
            $leftBuy = $buy['number'] - $buy['buy_number'];
            //还要卖的量
            $leftSale = $sale['number'] - $sale['sale_number'];
            //交易数量
            $num = min($leftSale, $leftBuy);
            if ($number == 0 || $number > $num) {
                return false;
            }
            $this->trade($number, $sale, $buy, 4);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
    }


    /**
     * 设置错误信息
     *
     * @param $error 错误信息
     * @return
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ? $this->_error : '';
    }

}
