<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Log;

/**
 * 商城交易接口
 */
class Shop extends Api
{

    protected $noNeedLogin = ['index'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 立即购买商品
     */
    public function buy()
    {
        $id = $this->request->request('gid');
        $num = $this->request->request('goods_num');
        $spec_key_name = $this->request->request('spec_key_name');
        $spec_key = $this->request->request('spec_key');
        $this->com_add($id, $num, 1, $spec_key_name, $spec_key);
        $this->success('订单确认', '', url('mobile/cart/cart2'));
    }

    /**
     * 加入购物车
     */
    public function addcart()
    {
        $id = $this->request->request('gid');
        $num = $this->request->request('goods_num');
        $spec_key_name = $this->request->request('spec_key_name');
        $spec_key = $this->request->request('spec_key');
        $this->com_add($id, $num, 0, $spec_key_name, $spec_key);
        $this->success('加入成功', '', url('mobile/cart/cart'));
    }

    /**
     * 加入购物车 公共方法
     * @param $id
     * @param $num
     */
    protected function com_add($id, $num, $state, $spec_key_name, $spec_key)
    {
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $goods = Db::name('shop_goods')->where(array('goods_id' => $id))->find();
            if ($goods['store_count'] == 0) {
                $this->error('库存不足' . $goods['store_count']);
            }
            if ($goods['is_on_sale'] == 0) {
                $this->error('商品已下架');
            }
            $user = $this->auth->getUserinfo();
            if (!$user['address'] || !$user['mobile'] || !$user['consignee']) {
                $this->error("请完善个人信息", '', url('index/Info/editinfo2'));
            }
            Db::name('cart')->where(['user_id' => $user['id']])->setField(['selected' => 0]);
            $info = Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id, 'spec_key' => $spec_key])->find();
            if ($info) {
                Db::name('cart')->where(['id' => $info['id']])->setField(['goods_num' => $num, 'selected' => $state]);
            } else {
                $price = $spec_key == 0 ? $goods['shop_price'] : $spec_key;
                //增加购物车
                Db::name('cart')->insertGetId([
                    'user_id'       => $user['id'],
                    'goods_id'      => $id,
                    'goods_name'    => $goods['goods_name'],
                    'goods_num'     => $num,   //商品数量
                    'market_price'  => $goods['market_price'],
                    'goods_price'   => $price,
                    'shop_id'       => $goods['shop_id'],
                    'add_time'      => time(),
                    'selected'      => $state,
                    'spec_key_name' => $spec_key_name,   //商品规格名称
                    'spec_key'      => $spec_key,   //商品规格价格

                ]);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
    }

    /**
     * 移除购物车
     */
    public function delcart()
    {
        $id = $this->request->request('gid');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();
            Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id])->delete();
            Db::commit();
            $this->success('删除成功', '', url('mobile/cart/cart'));

        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 购物车加
     */
    public function upcart()
    {
        $id = $this->request->request('gid');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();
            Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id])->setInc('goods_num', 1);
            Db::commit();
            $this->success('', '', url('mobile/cart/cart'));

        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 购物车减
     */
    public function downcart()
    {
        $id = $this->request->request('gid');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();
            $info = Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id])->find();
            if ($info['goods_num'] > 1) {
                Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id])->setDec('goods_num', 1);
            } else {
                $this->error('数量为零');
            }
            Db::commit();
            $this->success('', '', url('mobile/cart/cart'));

        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 购物选择
     */
    public function chosecart()
    {
        $id = $this->request->request('gid');
        $chose = $this->request->request('chose');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();
            Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id])->setField('selected', $chose);
            Db::commit();
            $this->success('', '', url('mobile/cart/cart'));
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 购物车 店铺选择
     */
    public function choseshop()
    {
        $id = $this->request->request('gid');
        $chose = $this->request->request('chose');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();
            Db::name('cart')->where(['user_id' => $user['id'], 'shop_id' => $id])->setField('selected', $chose);
            Db::commit();
            $this->success('', '', url('mobile/cart/cart'));
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 普通商品 购物车 结算
     */
    public function suancart()
    {
        $myset = config('site');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $walltype = config('site.walltype');
            $user = $this->auth->getUserinfo();
            if ($user['jointime'] == 0) {
                $this->error('请激活账号再操作！', '', '');
            }
            if($user['star']<$myset['shopxin']){
                $this->error('信用达到'.$myset['shopxin'].',才可以交易,你的信用是'.$user['star'] );
            }
            if($user['slrate']<$myset['shopsl']){
                $this->error('算力达到'.$myset['shopsl'].'G,才可以交易,你的算力是'.$user['slrate'].'G' );
            }
            if (empty($user['address']) || empty($user['shouphone']) || empty($user['consignee'])) {
                $this->error('请完善收货地址！', '', url('index/info/editinfo2'));
            }
            $ptype = input('ptype');                    //支付类型
            $shipping_code = input('shipping_code', '申通');    //物流id

            $list = Db::name('cart')->alias('a')
                ->join('fa_shop_goods b', 'b.goods_id = a.goods_id')
                ->where(['user_id' => $user['id'], 'selected' => 1])->select();
            //总费用
            $total = 0;
            $tmp=[];
            foreach ($list as $item) {

                $total += ($item['goods_price'] * $item['goods_num']);
                if ($item['goods_num'] > $item['store_count']) {
                    $this->error($item['goods_name'] . '库存为' . $item['store_count'] . ',请重新选择');
                }
                $tmp[$item['shop_id']][] = $item;
            }

            //扣款
            $row = json_decode(file_get_contents('https://www.huobi.co/-/x/general/index/constituent_symbol/detail'), true);
            $lists = $row['data']['symbols'];
            foreach ($lists as $vo) {
                $binfo[$vo['symbol']] =$vo;
            }
            switch ($ptype){
                case 'wall2':    //lmc
                    $lmcinfo = db('kline')->order('id','desc')->cache(true, 600)->find();
                    $lmcprice = $lmcinfo['close'] * $myset['usd2cny'];
                    $need = round($total/$lmcprice,6);
                    break;
                case 'wall3':    //btc
                    $btcprice = $binfo['btcusdt']['close']* $myset['usd2cny'];
                    $need = round($total/$btcprice,6);
                    break;
                case 'wall4':    //eth
                    $btcprice = $binfo['ethusdt']['close']* $myset['usd2cny'];
                    $need = round($total/$btcprice,6);
                    break;
                case 'wall5':    //ltc
                    $btcprice = $binfo['ltcusdt']['close']* $myset['usd2cny'];
                    $need = round($total/$btcprice,6);
                    break;
                case 'wall6':    //eos
                    $btcprice = $binfo['eosusdt']['close']* $myset['usd2cny'];
                    $need = round($total/$btcprice,6);
                    break;
                default:
                    $need = $total;
            }
            $needfee=$need*($myset['shopczfee']*0.01+1);
            //   判断授信金额是否够
            if ($user['wall7'] < $need) {
                $this->error('授信额度不足');
            }
            //   判断金额是否够
            if ($user[$ptype] < $needfee) {
                $this->error('金额不足');
            }
            caiwu($user['id'], -$needfee, 5, $ptype, '购买商品');
            caiwu($user['id'], -$need, 5, 'wall7', '购买商品');

            foreach ($tmp as $key => $goods) {
//                dump('店铺id'.$key);
//                dump($goods);
                unset($ordergoods);
                $orderprice = 0;
                foreach ($goods as $good) {
                    $orderprice += ($good['goods_price'] * $good['goods_num']);
                }
                //增加订单
                $ordersn = 'SHOP' . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                $orderid = Db::name('shop_order')->insertGetId([
                    'order_sn'      => $ordersn,
                    'user_id'       => $user['id'],
                    'consignee'     => $user['consignee'],
                    'address'       => $user['address'],
                    'mobile'        => $user['shouphone'],
                    'goods_price'   => $orderprice,
                    'total_amount'  => $orderprice,
                    'add_time'      => time(),
                    'shipping_name' => $shipping_code,   //快递
                    'order_status'  => 1,   //已付款待发货
                    'ptype'         => $ptype,
                    'shop_id'       => $key,
                    'pay_name'      => $walltype[$ptype],    //支付方式
                ]);
                foreach ($goods as $good) {
                    $ordergoods[] = [
                        'order_id'      => $orderid,
                        'goods_id'      => $good['goods_id'],
                        'goods_name'    => $good['goods_name'],
                        'goods_num'     => $good['goods_num'],   //商品数量
                        'market_price'  => $good['market_price'],
                        'goods_price'   => $good['shop_price'],
                        'cost_price'    => $good['cost_price'],
                        'spec_key_name' => $good['spec_key_name'],   //商品规格名称
                        'spec_key'      => $good['spec_key'],   //商品规格价格
                    ];
                    Db::name('shop_goods')->where(array('goods_id' => $good['goods_id']))->setDec('store_count', $good['goods_num']);   //减少库存

                }
                //增加订单商品
                Db::name('shop_order_goods')->insertAll($ordergoods);
            }

            //删除购物车
            Db::name('cart')->where(['user_id' => $user['id'], 'selected' => 1])->delete();

            Db::commit();
            $this->success('提交成功', '', url('index/user/order'));
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 改变订单状态
     */
    public function c_order()
    {
        $id = $this->request->request('gid');
        $state = $this->request->request('state');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $order = Db::name('shop_order')->find($id);
            //确认收货
            if ($state == '3' && $order['order_status']!='3') {
                Db::name('shop_order')->where(['order_id' => $order['order_id']])->setField(['confirm_time' => time(), 'order_status' => '3']);

                if ($order['shop_id'] != 0) {
                    caiwu($order['shop_id'], $order['total_amount'], 32, 'wall2', '卖出商品' . $order['order_sn']);

                }
            } else {
                $this->error('已经确认，请勿重复确认', '321');
            }
            Db::commit();
            $this->success('操作成功', '321');
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 收藏商品
     */
    public function collection()
    {
        $user = $this->auth->getUserinfo();
        $gid = input('gid');
        $collect = array();
        if (empty($user['collection'])) {
            $collect[] = $gid;
            $new = $collect;
        } else {
            $new = json_decode($user['collection'], true);

            if (in_array($gid, $new)) {
                $this->error('已收藏');
            } else {
                array_push($new, $gid);
            }

        }
        $new = json_encode($new);
        $re = Db::name('user')->where('id', $user['id'])->setField('collection', $new);
        if($re){
            $this->success('收藏成功');
        }else{
            $this->error('收藏失败');
        }

    }

    /**
     * 商家发布商品
     * @return \think\response\Json
     */
    public function uploadimg()
    {
        $urls=controller('common')->upload2();
        $data['status']=1;
        $data['url']=reset($urls);
        return json($data);

    }
    /**
     * 商家发布商品
     * @return \think\response\Json
     */
    public function uploadvideo()
    {
        $urls=controller('common')->uploadvideo();
        $data['status']=1;
        $data['url']=reset($urls);
        return json($data);

    }


}
